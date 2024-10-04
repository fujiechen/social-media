<x-mail::message>

<h1 style="text-align: center;">
欢迎 {{ $user }}
</h1>

    We're happy to have you with us at Capital Group Trust!

    Now you can deposit USDT to the platform, and start your investing.

    ## How to get started
    1. Prepare USDT on your own wallet, and use Deposit feature to deposit USDT to the platform
    2. Purchase any product that you are interested in with
    - You can do the exchange between different currency accounts, or transfer fund to other platform users
    3. Withdraw the available balance from your account to wired bank account or cash to your address

    Check out our guide for the detailed instructions and an overview of all Capital Group Trust features.

    Thanks
    Capital Group Trust

<x-mail::button :url="config('app.frontend_url')">
Get started
</x-mail::button>

<x-slot:subcopy>
*This is an automated message, please do not reply.*
</x-slot:subcopy>

</x-mail::message>
