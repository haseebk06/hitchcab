@component('mail::message')
<br />
<h1 style="font-size: 2rem; font-family: Nunito, sans-serif; font-weight: 700; line-height: 1.1; text-align: center; color: #364a63;">Verification Code</h1>
<br />
<h2 style="font-size: 2rem; font-family: 'Inter', sans-serif; font-weight: 700; line-height: 1.1; text-align: center; color: #364a63;">{{ $user->verificaton_token }}</hi>
<br />
<br />
<p style="font-size: 11px; font-family: Nunito, sans-serif; color: #222; line-height: 25px; text-align: center; display: block;">If you did not request this code, you can ignore this email.</p>
<br />
@endcomponent
