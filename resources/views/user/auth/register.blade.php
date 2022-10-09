@extends('user.layout.app')

@section('content')

<div class="page-header" style="background: no-repeat 60%/cover url({{ asset('assets/images/elements/page-header-auth.jpg') }});">
    <div class="container d-flex flex-column align-items-center">
        <nav aria-label="breadcrumb" class="breadcrumb-nav">
            <div class="container">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">HOME</a></li>
                    <li class="breadcrumb-item active text-dark" aria-current="page">
                        SignUp
                    </li>
                </ol>
            </div>
        </nav>
        <h1 class="text-uppercase">SignUp</h1>
    </div>
</div>

<div class="container-fluid login-container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-6">
                    <img src="{{ asset('assets/images/bg-auth.jpg') }}" />
                </div>
                <div class="col-md-6 pt-5">
                    @if(session()->has('message'))
                    <div class="alert alert-rounded alert-success alert-dismissible">
                        <span>{{ session()->get('message') }}</span>
                    </div>
                    @endif
                    @if($errors->any())
                    <div class="alert alert-rounded alert-danger alert-dismissible">
                        <span>{{$errors->first()}}</span>
                    </div>
                    @endif
                    <form action="{{ route('register.submit') }}" method="POST" id="form_register">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label for="name">{{ __('label.first_name') }}<span class="required">*</span></label>
                                <input type="text" class="form-input form-wide form-control" id="first_name" name="first_name" value="{{ old('first_name') }}" />
                            </div>
                            <div class="form-group col-6">
                                <label for="name">{{ __('label.last_name') }}<span class="required">*</span></label>
                                <input type="text" class="form-input form-wide form-control" id="last_name" name="last_name" value="{{ old('last_name') }}" />
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label for="email">{{ __('label.email') }}<span class="required">*</span></label>
                                <input type="email" class="form-input form-wide form-control" id="semail" name="email" value="{{ old('email') }}" />
                            </div>
                            <div class="form-group col-6">
                                <label for="name">Phone<span class="required">*</span></label>
                                <input type="text" class="form-input form-wide form-control" id="phone" name="phone" value="{{ old('phone') }}" />
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label for="email">Country<span class="required">*</span></label>
                                <select class="form-control" name="country">
                                    <option value="233" {{ old('country') == '233' ? 'selected' : '' }}>United States</option>
                                    <option value="232" {{ old('country') == '232' ? 'selected' : '' }}>United Kingdom</option>
                                    <option value="39" {{ old('country') == '39' ? 'selected' : '' }}>Canada</option>
                                </select>
                            </div>
                            <div class="form-group col-6">
                                <label for="name">ZIP Code</label>
                                <input type="text" class="form-input form-wide form-control" id="post_code" name="post_code" value="{{ old('post_code') }}" />
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-6">
                                <label for="password">{{ __('label.password') }}<span class="required">*</span></label>
                                <input type="password" class="form-input form-wide form-control" id="password" name="password" value="{{ old('password') }}" />
                            </div>
                            <div class="form-group col-6">
                                <label for="confirm-password">{{ __('label.confirm_password') }}<span class="required">*</span></label>
                                <input type="password" class="form-input form-wide form-control" id="confirm-password" name="confirm_password" value="{{ old('confirm_password') }}" />
                            </div>
                        </div>

                        {{-- {!! htmlFormSnippet() !!} --}}

                        <button type="submit" class="btn btn-dark btn-md w-100 mb-1 mt-2">SignUp</button>
                        <div class="text-center">
                            <span class="text-muted">Already have an account?</span>
                            <a href="{{ route('login') }}" class="font-weight-bold">SignIn</a>
                        </div>
                        {{-- <div class="text-center mt-3">
                            <span>- {{ __('message.login_with_social') }} -</span>
                        </div>
                        <div class="social-icons text-center mt-1">
                            <a class="btn btn-info btn-icon-left btn-rounded btn-md mr-3" href="{{route('login.redirect','facebook')}}" ><i class="fab fa-facebook-f mr-2"></i>Facebook</a>
                            <a class="btn btn-danger btn-icon-left btn-rounded btn-md" href="{{route('login.redirect','google')}}" ><i class="fab fa-google mr-2"></i>Google+</a>
                        </div> --}}
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('page-script')

<script type="text/javascript">

    $("#form_register").validate({
        rules: {
            first_name: {
                required: true,
            },
            last_name: {
                required: true,
            },
            phone: {
                required: true,
            },
            email: {
                required: true,
                email: true,
                remote: {
                    url: '{{ route('check-email') }}',
                    type: "post",
                    data: {
                        email: $("#email").val(),
                        _token: "{{ csrf_token() }}"
                    },
                    dataFilter: function (data) {

                        var json = JSON.parse(data);

                        if(json.msg == 'exists'){
                            return "\"" + "This email address is already in use." + "\"";
                        } else {
                            return "true";
                        }
                    }
                }
            },
            password: {
                required: true,
                minlength: 6
            },
            confirm_password: {
                required: true,
                equalTo: '#password'
            },
        },
        messages: {
            first_name: {
                required: "{{ __('message.validation.required') }}",
            },
            last_name: {
                required: "{{ __('message.validation.required') }}",
            },
            phone: {
                required: "Phone Number required",
            },
            email: {
                required: "{{ __('message.validation.required') }}",
                email: "{{ __('message.validation.invalid_email') }}",
                remote: "{{ __('message.validation.email_exist') }}",
            },
            password: {
                required: "{{ __('message.validation.password_required') }}",
                minlength: "{{ __('message.validation.password_length_error') }}",
            },
            confirm_password: {
                required: "{{ __('message.validation.confirm_password_required') }}",
                equalTo: "{{ __('message.validation.confirm_password_error') }}",
            },
        },
    });

</script>

@endpush
