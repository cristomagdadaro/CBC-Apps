@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
@if (trim($slot) === 'Laravel')
<img src="https://laravel.com/img/notification-logo-v2.1.png" class="logo" alt="Laravel Logo">
@else
<span style="font-size: 32px; font-weight: 700; color: #84cc16; font-family: 'Montserrat', sans-serif;">OneCBC Portal</span><br>
<span style="font-size: 11px; font-weight: 300; color: #84cc16; font-family: 'Montserrat', sans-serif;">by DA-Crop Biotechnology Center</span>
@endif
</a>
</td>
</tr>
