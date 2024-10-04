<x-mail::message>

<h1 style="text-align: center;">
重置密码
</h1>

您收到此电子邮件是因为我们收到了您帐户的密码重置请求。

<x-mail::button :url="$resetUrl">
重置密码
</x-mail::button>

<x-slot:subcopy>
如果您没有请求重置密码，则无需采取进一步措施。
</x-slot:subcopy>

谢谢,\
{{ config('app.name') }}
</x-mail::message>
