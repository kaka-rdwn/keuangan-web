<x-mail::message>
# Yth. {{ $user->name }},

Selamat datang di Sistem Manajemen Keuangan! Akun Anda telah berhasil dibuat oleh Administrator.

Berikut adalah detail kredensial akses akun Anda:

<x-mail::panel>
**Email Login:** {{ $user->email }}  
**Password Sementara:** {{ $plainPassword }}  
**Peran (Role):** {{ $user->role?->name ?? 'User' }}
</x-mail::panel>

Demi keamanan akun Anda, harap segera login dan lakukan perubahan password pada menu Pengaturan Profil setelah login pertama kali.

<x-mail::button :url="route('login')">
Login ke Sistem
</x-mail::button>

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
