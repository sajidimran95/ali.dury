<?php

namespace Modules\Ecommerce\Http\Controllers\Front;

use App\Contact;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Ecommerce\Entities\EcomCustomerAddress;
use Modules\Ecommerce\Entities\EcomCustomerLicense;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('ecommerce::front.auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $business_id = $request->attributes->get('ecommerce_business_id');
        $contact = Contact::where('business_id', $business_id)
            ->onlyCustomers()
            ->where(function ($q) use ($data) {
                $q->where('email', $data['login'])->orWhere('mobile', $data['login']);
            })
            ->first();

        if (! $contact || empty($contact->password) || ! Hash::check($data['password'], $contact->password)) {
            return back()->withInput()->with('error', __('ecommerce::lang.invalid_credentials'));
        }

        Auth::guard('customer')->login($contact, $request->boolean('remember'));

        return redirect()->route('ecommerce.account')->with('success', __('ecommerce::lang.welcome_back'));
    }

    public function showRegister()
    {
        return view('ecommerce::front.auth.register', [
            'us_states' => $this->usStates(),
        ]);
    }

    public function register(Request $request)
    {
        $business_id = $request->attributes->get('ecommerce_business_id');

        $data = $request->validate([
            'business_name' => 'required|string|max:191',
            'mobile' => 'required|string|max:30',
            'tax_number' => 'nullable|string|max:50',
            'tax_exempt_code' => 'nullable|string|max:50',
            'contact_name' => 'required|string|max:191',
            'email' => 'required|email|max:191',
            'billing_email' => 'nullable|email|max:191',
            'billing_same_email' => 'nullable',
            'password' => 'required|string|min:7|max:16|confirmed',
            'address_line_1' => 'required|string|max:191',
            'address_line_2' => 'nullable|string|max:191',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:50',
            'zip_code' => 'required|string|max:20',
            'country' => 'nullable|string|max:50',
            'licenses' => 'nullable|array',
            'licenses.*.type' => 'nullable|string|max:50',
            'licenses.*.label' => 'nullable|string|max:191',
            'licenses.*.number' => 'nullable|string|max:191',
            'licenses.*.expires_at' => 'nullable|date',
            'licenses.*.file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
            'agree_terms' => 'accepted',
        ]);

        if (preg_match('/\s/', $data['password'])) {
            return back()->withInput()->with('error', 'Password cannot contain spaces.');
        }

        $nameParts = preg_split('/\s+/', trim($data['contact_name']), 2);
        $first = $nameParts[0] ?? $data['contact_name'];
        $last = $nameParts[1] ?? '';

        $billingEmail = $request->boolean('billing_same_email')
            ? $data['email']
            : ($data['billing_email'] ?: $data['email']);

        $exists = Contact::where('business_id', $business_id)
            ->where(function ($q) use ($data) {
                $q->where('email', $data['email'])->orWhere('mobile', $data['mobile']);
            })
            ->first();

        if ($exists && ! empty($exists->password)) {
            return back()->withInput()->with('error', __('ecommerce::lang.account_exists'));
        }

        $payload = [
            'business_id' => $business_id,
            'type' => 'customer',
            'name' => $data['contact_name'],
            'supplier_business_name' => $data['business_name'],
            'ecom_first_name' => $first,
            'ecom_last_name' => $last,
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'tax_number' => $data['tax_number'] ?? null,
            'custom_field1' => $data['tax_exempt_code'] ?? null,
            'custom_field2' => $billingEmail,
            'address_line_1' => $data['address_line_1'] ?? null,
            'address_line_2' => $data['address_line_2'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'zip_code' => $data['zip_code'] ?? null,
            'country' => $data['country'] ?? 'United States',
            'password' => Hash::make($data['password']),
            'created_by' => 1,
            'contact_status' => 'active',
            'custom_field6' => \Modules\Ecommerce\Support\WholesaleAccount::PENDING,
        ];

        if ($exists) {
            $exists->fill($payload);
            $exists->save();
            $contact = $exists;
        } else {
            $contact = Contact::create($payload);
        }

        EcomCustomerAddress::create([
            'business_id' => $business_id,
            'contact_id' => $contact->id,
            'first_name' => $first,
            'last_name' => $last ?: $first,
            'company' => $data['business_name'],
            'address_line_1' => $data['address_line_1'],
            'address_line_2' => $data['address_line_2'] ?? null,
            'city' => $data['city'],
            'state' => $data['state'],
            'zip_code' => $data['zip_code'],
            'country' => $data['country'] ?? 'United States',
            'phone' => $data['mobile'],
            'is_default' => 1,
        ]);

        $this->storeLicenses($request, (int) $business_id, (int) $contact->id);

        Auth::guard('customer')->login($contact);

        return redirect()->route('ecommerce.account')->with('success', 'Account created. Your wholesale account is pending approval — you can upload/view licenses while we review.');
    }

    protected function storeLicenses(Request $request, int $business_id, int $contact_id): void
    {
        $licenses = $request->input('licenses', []);
        $files = $request->file('licenses', []);

        foreach ($licenses as $key => $row) {
            $number = trim((string) ($row['number'] ?? ''));
            $expires = $row['expires_at'] ?? null;
            $file = $files[$key]['file'] ?? null;

            if ($number === '' && empty($expires) && empty($file)) {
                continue;
            }

            $path = null;
            $original = null;
            if ($file) {
                $publicDir = public_path('uploads/ecom_licenses/'.$contact_id);
                if (! is_dir($publicDir)) {
                    mkdir($publicDir, 0755, true);
                }
                $original = $file->getClientOriginalName();
                $filename = uniqid('lic_', true).'.'.$file->getClientOriginalExtension();
                $file->move($publicDir, $filename);
                $path = 'ecom_licenses/'.$contact_id.'/'.$filename;
            }

            EcomCustomerLicense::create([
                'business_id' => $business_id,
                'contact_id' => $contact_id,
                'license_type' => $row['type'] ?? 'other',
                'label' => $row['label'] ?? ucfirst((string) $key),
                'certificate_number' => $number ?: null,
                'expires_at' => $expires ?: null,
                'file_path' => $path,
                'original_name' => $original,
            ]);
        }
    }

    public function logout()
    {
        Auth::guard('customer')->logout();

        return redirect()->to('/');
    }

    protected function usStates(): array
    {
        return [
            'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas', 'CA' => 'California',
            'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware', 'FL' => 'Florida', 'GA' => 'Georgia',
            'HI' => 'Hawaii', 'ID' => 'Idaho', 'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa',
            'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
            'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi', 'MO' => 'Missouri',
            'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada', 'NH' => 'New Hampshire', 'NJ' => 'New Jersey',
            'NM' => 'New Mexico', 'NY' => 'New York', 'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio',
            'OK' => 'Oklahoma', 'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
            'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah', 'VT' => 'Vermont',
            'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia', 'WI' => 'Wisconsin', 'WY' => 'Wyoming',
            'DC' => 'District of Columbia',
        ];
    }
}
