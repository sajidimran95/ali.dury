@php
    $accountNav = [
        ['n' => '1', 'label' => 'Edit your account information', 'route' => 'ecommerce.account.profile'],
        ['n' => '2', 'label' => 'Change your password', 'route' => 'ecommerce.account.password'],
        ['n' => '3', 'label' => 'Modify your address book entries', 'route' => 'ecommerce.account.addresses'],
        ['n' => '4', 'label' => 'Order history', 'route' => 'ecommerce.account.orders'],
        ['n' => '5', 'label' => 'Licenses', 'route' => 'ecommerce.account.licenses'],
    ];
    $current = $current ?? '';
@endphp
<div class="myaccount-area">
    <h1 class="shopping-cart-heading">My Account</h1>
    <div class="myaccount-wrapper">
        @foreach($accountNav as $item)
            @php $on = $current === $item['route'] || ($current === 'ecommerce.account' && $item['route'] === 'ecommerce.account.profile'); @endphp
            <div class="single-my-account {{ $on ? 'is-open' : '' }}">
                <a class="panel-title" href="{{ route($item['route']) }}"><span>{{ $item['n'] }} .</span> {{ $item['label'] }}</a>
                @if($on)
                    <div class="myaccount-info-wrapper">
                        {{ $slot }}
                    </div>
                @endif
            </div>
        @endforeach
        <div class="single-my-account">
            <form action="{{ route('ecommerce.logout', absolute: false) }}" method="post">
                @csrf
                <button type="submit" class="panel-title" style="width:100%;cursor:pointer;font:inherit;text-align:left"><span>6 .</span> Logout</button>
            </form>
        </div>
    </div>
</div>
