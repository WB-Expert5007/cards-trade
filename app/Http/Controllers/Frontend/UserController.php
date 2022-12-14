<?php

namespace app\Http\Controllers\Frontend;

use app\Http\Controllers\Controller;
use App\Http\Controllers\Support\EmailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\User;
use App\Models\UserVerify;
use App\Models\Cart;
use App\Models\Order;
use App\Models\offers;
use App\Models\OrderList;
use Lang;
use Auth;
use Mail;
use Session;
use Helper;
use DB;

class UserController extends Controller {
    /* Render Login Page */

    public function login() {
        $title = 'SignIn';
        return view( 'frontend.auth.login' )->withTitle( $title );
    }

    public function ajaxLogin() {
        return view( 'user.auth.login' )->render();
    }

    /* Ajax Login */

    public function loginSubmit( Request $request ) {
        if ( !empty( $request->email ) && !empty( $request->password ) ) {
            $email = User::where( 'email', $request->email )->first();

            if ( !empty( $email ) ) {
                $status = User::where( [ 'email' => $request->email, 'status' => 'active' ] )->first();

                if ( !empty( $status ) ) {
                    if ( Auth::guard( 'user' )->attempt( [ 'email' => $request->email, 'password' => $request->password ] ) ) {
                        if ( Session::has( 'carts' ) ) {
                            foreach ( Session::get( 'carts' ) as $cart ) {
                                $carModel = new Cart;
                                $carModel->user_id = Auth::user()->id;
                                $carModel->product_id = $cart->id;
                                $carModel->quantity = 1;
                                $carModel->save();
                            }
                            Session::forget( 'carts' );
                        }
                        $offer = Helper::redirectGateway();
                        if ( !Session::has( 'offerId' ) ) {
                            Session::forget('offerId');
                            return redirect()->route( 'home' );
                        }
                        if ( Session::has( 'offerId' ) ) {
                            Session::forget('offerId');
                            $data[ 'offer' ] = $offer;
                            return view( 'frontend.gateway', $data );
                        }
                        if ( $request->redirect_url ) {
                            return redirect()->route( 'gateway' );
                        } else {
                            return redirect()->route( 'home' );
                        }
                    } else {
                        return back()->withErrors( Lang::get( 'message.response.invalid_login' ) )->withInput();
                    }
                } else {
                    return back()->withErrors( Lang::get( 'message.response.inactive_user' ) )->withInput();
                }
            } else {
                return back()->withErrors( Lang::get( 'message.response.invalid_login' ) )->withInput();
            }
        } else {
            return back()->withErrors( Lang::get( 'message.response.required_fields' ) )->withInput();
        }
    }

    /* Render Register Page */

    public function register() {
        $title = 'SignUp';
        return view( 'frontend.auth.register' )->withTitle( $title );
    }

    /* Ajax Check Email Exist */

    public function checkEmail( Request $request ) {
        $email = $request->email;
        $isExists = User::where( 'email', $email )->first();

        if ( $isExists ) {
            return response()->json( array( 'msg' => 'exists' ) );
        } else {
            return response()->json( array( 'msg' => 'notexists' ) );
        }
    }

    /* Ajax Save New User */

    public function registerSave( Request $request, EmailController $email_controller ) {
        $rules = array(
            'first_name' => 'required',
            'last_name' => 'required',
            'phone' => 'required',
            'email' => 'string|required|unique:users,email',
            'password' => 'required|min:6',
            // 'g-recaptcha-response' => 'recaptcha',
        );

        // Add User Validation Custom Names
        $niceNames = array(
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'phone' => 'Phone Number',
            'email' => 'Email',
            'password' => 'Password',
        );

        $validator = Validator::make( $request->all(), $rules );
        $validator->setAttributeNames( $niceNames );

        if ( $validator->fails() ) {
            $messages = $validator->messages();
            return back()->withErrors( $validator )->withInput();
        } else {
            $user = new User;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->phone = $request->phone;
            $user->post_code = $request->post_code;
            $user->email = $request->email;
            $user->country = $request->country;
            $user->password = Hash::make( $request->password );
            $user->save();

            if ( Auth::guard( 'user' )->attempt( [ 'email' => $request->email, 'password' => $request->password ] ) ) {
                $offer = Helper::redirectGateway();
                if ( !Session::has( 'offerId' ) ) {
                    Session::forget('offerId');
                    return redirect()->route( 'home' );
                }
                if ( Session::has( 'offerId' ) ) {
                    $data[ 'offer' ] = $offer;
                    Session::forget('offerId');
                    return view( 'frontend.gateway', $data );
                }
            } else {
                return back()->withErrors( 'Sorry, Database Connection Error.' );
            }

            // $token = Str::random( 64 );

            // UserVerify::create( [
            //     'user_id' => $user->id,
            //     'token' => $token
            // ] );

            // if ( $email_controller->emailVerification( $request->email, $token ) ) {

            //     if ( Auth::guard( 'user' )->attempt( [ 'email' => $request->email, 'password' => $request->password ] ) )
            // {
            //         return redirect()->route( 'home' );
            //     }

            // } else {
            //     return back()->withErrors( 'Sorry, Database Connection Error.' );
            // }
        }
    }

    /* Send Verification Email */

    public function resendVerifyEmail( EmailController $email_controller ) {
        $user = User::find( Auth::guard( 'user' )->user()->id );

        $token = Str::random( 64 );

        UserVerify::create( [
            'user_id' => $user->id,
            'token' => $token
        ] );

        if ( $email_controller->emailVerification( $user->email, $token ) ) {
            return back();
        } else {
            return back();
        }
    }

    /* Confirm Email Verify */

    public function verifyEmail( Request $request ) {
        $user = UserVerify::where( 'token', $request->token )->get();
        if ( count( $user ) > 0 ) {
            $user_info = User::find( $user[ 0 ]->user_id );

            if ( $user_info->email == $request->email ) {
                $user_info->email_verified = 1;
                $user_info->email_verified_at = date( 'Y-m-d H:i:s' );
                $user_info->save();

                UserVerify::where( 'user_id', $user_info->id )->delete();

                return redirect()->route( 'verify.success', app()->getLocale() );
            }
        }
        return abort( 500 );
    }

    /* Render Verification Success Page */

    public function verifySuccess() {
        return view( 'frontend.pages.verify_success' );
    }

    /* Render Forgot Password */

    public function forgotPassword() {
        $title = Lang::get( 'title.forgot_password' );
        return view( 'frontend.pages.forgot-password' )->withTitle( $title );
    }

    /* Reset Password Email with Token */

    public function resetPasswordToken( Request $request, EmailController $email_controller ) {
        $user = User::where( 'email', '=', $request->email )->first();

        if ( empty( $user ) ) {
            return back()->withErrors( Lang::get( 'message.response.user_not_exist' ) );
        }

        //Create Password Reset Token
        DB::table( 'password_resets' )->insert( [
            'email' => $request->email,
            'token' => Str::random( 60 ),
        ] );

        //Get the token just created above
        $tokenData = DB::table( 'password_resets' )
        ->where( 'email', $request->email )
        ->first();

        if ( $email_controller->forgot_password( $request->email, $tokenData->token ) ) {
            return back()->with( 'message', Lang::get( 'message.response.reset_email_success' ) );
        } else {
            return back()->withErrors( Lang::get( 'message.response.unknown_error' ) );
        }
    }

    /* Reset Password Page */

    public function reset_password( $token ) {
        $title = Lang::get( 'title.reset_password' );
        return view( 'frontend.pages.reset_password' )->withTitle( $title );
    }

    /* Save Reset Password */

    public function resetPasswordSave( Request $request ) {
        $validator = Validator::make( $request->all(), [
            'password' => 'required',
            'token' => 'required'
        ] );

        if ( $validator->fails() ) {
            return back()->withErrors( Lang::get( 'message.response.required' ) );
        }

        $password = $request->password;

        $tokenData = DB::table( 'password_resets' )
        ->where( 'token', $request->token )
        ->first();

        if ( !$tokenData ) return redirect()->route( 'forgot-password', app()->getLocale() )->withErrors( Lang::get( 'message.response.reset_token_error' ) );

        $user = User::where( 'email', $tokenData->email )->first();

        if ( !$user ) return back()->withErrors( Lang::get( 'message.response.invalid_email' ) );

        $user->password = \Hash::make( $password );
        $user->update();

        DB::table( 'password_resets' )->where( 'email', $user->email )->delete();

        return redirect( app()->getLocale().'/login' )->with( 'message', Lang::get( 'message.response.change_password_success' ) );
    }

    /* Logout User */

    public function logout() {
        Auth::guard( 'user' )->logout();
        return redirect()->route( 'home' );
    }

    /* Render My Account Page */

    public function myAccount() {
        $title = 'My Account';
        $profile = User::find( Auth::user()->id );
        $data[ 'profile' ] = $profile;
        return view( 'frontend.user.my-account', $data )->withTitle( $title );
    }

    /* Render My Account - Download Page */

    public function downloads() {
        $title = 'Downloads';
        $data = array();
        return view( 'frontend.user.download', $data )->withTitle( $title );
    }

    /* Render My Account - Address Page */

    public function address() {
        $title = 'Addresses';
        $profile = User::find( Auth::user()->id );
        $data[ 'profile' ] = $profile;
        return view( 'frontend.user.addresses', $data )->withTitle( $title );
    }

    /* Render My Account - Profile Page */

    public function profile() {
        $title = 'Account Details';
        $profile = User::find( Auth::user()->id );
        $data[ 'profile' ] = $profile;
        return view( 'frontend.user.profile', $data )->withTitle( $title );
    }

    /* Ajax Update Profile */

    public function profileUpdate( Request $request ) {
        try {
            $user = User::find( Auth::user()->id );
            $old_email = $user->email;

            $user->first_name = $request->firstname;
            $user->last_name = $request->lastname;
            $user->email = $request->email;

            if ( $old_email != $request->email ) {
                $user->email_verified = 0;

                // $token = Str::random( 64 );
                // UserVerify::create( [
                //     'user_id' => $user->id,
                //     'token' => $token
                // ] );

                // $email_controller->emailVerification( $request->acc_email, $token );
            }

            $user->save();

            if ( $request->has( 'change_pass' ) ) {
                if ( Hash::check( $request->cur_password, $user->password ) ) {
                    $user->password = Hash::make( $request->new_password );
                    $user->save();

                    $res = array(
                        'status' => 'success',
                        'message' => 'Profile & Password has been updated successfully.'
                    );
                    return response()->json( $res );
                } else {
                    $res = array(
                        'status' => 'error',
                        'title' => 'Wrong Password',
                        'message' => 'Please type correct password.'
                    );
                    return response()->json( $res );
                }
            } else {
                $res = array(
                    'status' => 'success',
                    'message' => 'Profile has been updated successfully.'
                );
                return response()->json( $res );
            }
        } catch ( \Exception $e ) {
            $res = array(
                'status' => 'error',
                'title' => 'Server Disconnected',
                'message' => 'Something went wrong on serverside.'
            );
            return response()->json( $res );
        }
    }

    /* Render User Orders Page */

    public function orders() {
        $title = 'Orders';
        $orders = OrderList::where( 'user_id', Auth::user()->id )->get();
        $data[ 'orders' ] = $orders;
        return view( 'frontend.user.orders', $data )->withTitle( $title );
    }

    /* Render User Order Detail Page */

    public function orderDetail( Request $request ) {
        $title = 'Order #'.Helper::genID( $request->id );
        $order_data = Order::with( 'products' )->find( $request->id );
        if ( empty( $order_data ) ) return abort( 404 );
        $data[ 'content' ] = $order_data;
        return view( 'frontend.user.order-detail', $data )->withTitle( $title );
    }
}
