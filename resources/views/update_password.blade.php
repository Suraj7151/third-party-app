<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        @method('PUT')
    
        <!-- Current Password -->
        <div>
            <label for="current_password">Current Password</label>
            <input id="current_password" type="password" name="current_password" required autocomplete="current-password">
            @error('current_password')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    
        <!-- New Password -->
        <div>
            <label for="new_password">New Password</label>
            <input id="new_password" type="password" name="new_password" required autocomplete="new-password">
            @error('new_password')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    
        <!-- Confirm New Password -->
        <div>
            <label for="new_password_confirmation">Confirm New Password</label>
            <input id="new_password_confirmation" type="password" name="new_password_confirmation" required autocomplete="new-password">
            @error('new_password_confirmation')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    
        <button type="submit">Change Password</button>
    </form>
    
</body>
</html>