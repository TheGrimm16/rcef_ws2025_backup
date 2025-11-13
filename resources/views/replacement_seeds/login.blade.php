<form action="{{ route('replacement.login.submit') }}" method="POST">
    {{ csrf_field() }}

    @if(session()->has('warning'))
        <div class="text-yellow-600 mb-2">
            {{ session('warning') }}
        </div>
    @endif

    <div>
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required>
        @if($errors->has('email'))
            <span class="text-red-600">{{ $errors->first('email') }}</span>
        @endif
    </div>

    <div>
        <label>Password</label>
        <input type="password" name="password" required>
        @if($errors->has('password'))
            <span class="text-red-600">{{ $errors->first('password') }}</span>
        @endif
    </div>

    <button type="submit">Login</button>
</form>
