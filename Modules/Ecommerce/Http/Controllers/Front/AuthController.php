<?php

namespace Modules\Ecommerce\Http\Controllers\Front;

use App\Contact;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Ecommerce\Entities\EcomCustomerAddress;
use Modules\Ecommerce\Entities\EcomCustomerLicense;
use Modules\Ecommerce\Services\CartService;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        $this->forgetNonShopIntended($request);

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

        // Capture before Auth::login() — SessionGuard regenerates the session id.
        $guestSessionId = $request->session()->getId();

        Auth::guard('customer')->login($contact, $request->boolean('remember'));

        app(CartService::class)->claimGuestCart((int) $business_id, $guestSessionId);

        // Never send storefront customers to admin/POS intended URLs
        // (same browser session may still hold url.intended from /login).
        $target = $this->shopIntendedUrl($request) ?: route('ecommerce.account');

        return redirect()->to($target)->with('success', __('ecommerce::lang.welcome_back'));
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

        $guestSessionId = $request->session()->getId();

        Auth::guard('customer')->login($contact);

        app(CartService::class)->claimGuestCart((int) $business_id, $guestSessionId);

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

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $this->forgetNonShopIntended($request);

        return redirect()->to('/');
    }

    /**
     * Shop path prefix, e.g. "/shop".
     */
    protected function shopPrefix(): string
    {
        return '/'.trim((string) config('ecommerce.route_prefix', 'shop'), '/');
    }

    /**
     * Only allow redirects back into the storefront (never admin /login, /home, /pos, etc.).
     */
    protected function isShopUrl(?string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (! is_string($path) || $path === '') {
            return false;
        }

        $prefix = $this->shopPrefix();
        if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
            return true;
        }

        // Figma-site root aliases that share storefront controllers
        // (exclude /login /register /account /products — those belong to POS)
        $rootAliases = [
            '/cart', '/checkout', '/categories', '/promotions',
            '/new-arrivals', '/search', '/contact', '/about', '/faq', '/terms',
            '/privacy', '/shipping', '/returns', '/compliance', '/wholesale-agreement',
            '/forgot-password',
        ];

        foreach ($rootAliases as $alias) {
            if ($path === $alias || str_starts_with($path, $alias.'/')) {
                return true;
            }
        }

        return str_starts_with($path, '/product/')
            || str_starts_with($path, '/category/')
            || str_starts_with($path, '/brand/')
            || str_starts_with($path, '/collections/')
            || $path === '/account/apply';
    }

    protected function forgetNonShopIntended(Request $request): void
    {
        $intended = $request->session()->get('url.intended');
        if ($intended && ! $this->isShopUrl($intended)) {
            $request->session()->forget('url.intended');
        }
    }

    protected function shopIntendedUrl(Request $request): ?string
    {
        $intended = $request->session()->pull('url.intended');

        return $this->isShopUrl($intended) ? $intended : null;
    }

    public function showForgot()
    {
        return view('ecommerce::front.auth.forgot');
    }

    public function sendReset(Request $request)
    {
        $data = $request->validate(['email' => 'required|email']);
        $business_id = $request->attributes->get('ecommerce_business_id');
        $contact = Contact::where('business_id', $business_id)->onlyCustomers()->where('email', $data['email'])->first();

        if ($contact && Schema::hasTable('ecom_password_resets')) {
            $token = Str::random(48);
            DB::table('ecom_password_resets')->insert([
                'business_id' => $business_id,
                'email' => $data['email'],
                'token' => hash('sha256', $token),
                'expires_at' => now()->addHours(2),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $request->session()->flash('reset_debug_url', route('ecommerce.password.reset', ['token' => $token, 'email' => $data['email']]));
        }

        return back()->with('success', 'If that email is on file, you can continue with the reset link shown after this message when available.');
    }

    public function showReset(Request $request, string $token)
    {
        return view('ecommerce::front.auth.reset', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:7|max:16|confirmed',
        ]);
        $business_id = $request->attributes->get('ecommerce_business_id');
        if (! Schema::hasTable('ecom_password_resets')) {
            return back()->with('error', 'Reset is unavailable.');
        }
        $row = DB::table('ecom_password_resets')
            ->where('business_id', $business_id)
            ->where('email', $data['email'])
            ->where('token', hash('sha256', $data['token']))
            ->where('expires_at', '>', now())
            ->orderByDesc('id')
            ->first();
        if (! $row) {
            return back()->with('error', 'This reset link is invalid or expired.');
        }
        $contact = Contact::where('business_id', $business_id)->onlyCustomers()->where('email', $data['email'])->first();
        if (! $contact) {
            return back()->with('error', 'Account not found.');
        }
        $contact->password = Hash::make($data['password']);
        $contact->save();
        DB::table('ecom_password_resets')->where('id', $row->id)->delete();

        return redirect()->route('ecommerce.login')->with('success', 'Password updated. Sign in.');
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
