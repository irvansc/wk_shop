@extends('back.layout.auth-layout')

@section('pageTitle', isset($pageTitle) ? $pageTitle : "reset password")


@section('content')
<div class="login-box bg-white box-shadow border-radius-10">
    <div class="login-title">
        <h2 class="text-center text-primary">Reset Password</h2>
    </div>
    <h6 class="mb-20">Enter your new password, confirm and submit</h6>
    <form method="POST" action="{{ route('admin.reset-password-handler', ['token' => request()->token]) }}">
        @csrf
        @if (Session::get('fail'))
            <div class="alert alert-danger">
                {{ Session::get('fail') }}
                <button class="close" data-dismiss="alert" type="button" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

        @endif
        @if (Session::get('success'))
            <div class="alert alert-primary">
                {{ Session::get('success') }}
                <button class="close" data-dismiss="alert" type="button" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        <div class="input-group custom">
            <input type="password" class="form-control form-control-lg @error('new_password')
                form-control-danger
            @enderror" placeholder="New Password" value="{{ old('new_password') }}" name="new_password">
            <div class="input-group-append custom">
                <span class="input-group-text"><i class="dw dw-padlock1"></i></span>
            </div>
        </div>
        @error('new_password')
            <div class="form-control-feedback text-danger" style="margin-top: -25px;margin-bottom: 15px">
                {{ $message }}
            </div>
        @enderror
        <div class="input-group custom">
            <input type="password" class="form-control form-control-lg" placeholder="Confirm New Password" name="new_password_confirmation" value="{{ old('new_password_confirmation') }}">
            <div class="input-group-append custom">
                <span class="input-group-text"><i class="dw dw-padlock1"></i></span>
            </div>
        </div>
        @error('new_password_confirmation')
        <div class="form-control-feedback text-danger" style="margin-top: -25px;margin-bottom: 15px">
            {{ $message }}
        </div>
    @enderror
        <div class="row align-items-center">
            <div class="col-5">
                <div class="input-group mb-0">
                    <input class="btn btn-primary btn-lg btn-block" type="submit" value="Submit">
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
