<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="form-container">
        @csrf

        <h2 class="form-title">إنشاء حساب جديد</h2>

        <!-- Name -->
        <div class="form-group">
            <label for="name" class="form-label">الاسم</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="form-input" />
            <x-input-error :messages="$errors->get('name')" class="form-error" />
        </div>

        <!-- Email Address -->
        <div class="form-group">
            <label for="email" class="form-label">البريد الإلكتروني</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="form-input" />
            <x-input-error :messages="$errors->get('email')" class="form-error" />
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="password" class="form-label">كلمة المرور</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" class="form-input" />
            <x-input-error :messages="$errors->get('password')" class="form-error" />
        </div>

        <!-- Confirm Password -->
        <div class="form-group">
            <label for="password_confirmation" class="form-label">تأكيد كلمة المرور</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="form-input" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="form-error" />
        </div>

        <!-- Role selection -->
        <div class="form-group">
            <label for="role" class="form-label">الدور</label>
            <select id="role" name="role" class="form-input">
                <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>مستخدم</option>
                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>مشرف</option>
            </select>
            <x-input-error :messages="$errors->get('role')" class="form-error" />
        </div>

        <!-- Submit Button -->
        <div class="form-group">
            <button type="submit" class="form-button">تسجيل</button>
        </div>
    </form>

    <style>
        body {
            background-color: #F7F6F5;
            font-family: Tahoma, sans-serif;
            margin: 0;
            padding: 0;
            direction: rtl;
        }

        .form-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .form-title {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 25px;
            background: linear-gradient(to right, #F8E0C1, #D9B1AB);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-weight: bold;
            margin-bottom: 6px;
            color: #2F2F2F;
        }

        .form-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #E6E3E2;
            border-radius: 10px;
            font-size: 14px;
            transition: border 0.3s, box-shadow 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: #D9B1AB;
            box-shadow: 0 0 5px rgba(217,177,171,0.5);
        }

        .form-error {
            color: #e3342f;
            font-size: 12px;
            margin-top: 5px;
        }

        .form-button {
            width: 100%;
            padding: 12px;
            background-color: #D9B1AB;
            border: none;
            border-radius: 12px;
            color: #fff;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }

        .form-button:hover {
            background-color: #C89C96;
            transform: translateY(-2px);
        }
    </style>
</x-guest-layout>
