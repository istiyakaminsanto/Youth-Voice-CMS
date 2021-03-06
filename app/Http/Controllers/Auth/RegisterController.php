<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\SendEmailVerification;
use App\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'gender' => 'required|string|max:10',
            'birthdate' => 'required|date',
            'occupation' => 'required|string|max:100',
            'position' => 'required|string|max:20',
            'address' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['firstname']. ' '.$data['lastname'],
            'email' => $data['email'],
            'gender' => $data['gender'],
            'birthdate' => $data['birthdate'],
            'occupation' => $data['occupation'],
            'position' => $data['position'],
            'address' => $data['address'],
            'status' => 0,
            'verify_token' => md5($data['email'].rand(100,100000)),
            'password' => Hash::make($data['password']),
        ]);
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $user = $this->create($request->all());
        Mail::to($request['email'])->send(new SendEmailVerification($user));

        session()->flash('message', 'Created account successfully. Verify Your Email to active your accounts');
        return redirect('login');
    }

    public function verifySuccess($email, $verify_token)
    {
        $user = User::where([
            'email' => $email,
            'verify_token' => $verify_token
        ])->first();

        if ($user == NULL) {
            return 'page not fount';
        } else {
            User::where([
                'email' => $email,
                'verify_token' => $verify_token
            ])->update([
                'status' => 1,
                'verify_token' => NULL
            ]);

            session()->flash('message', 'Your Account is Activated. Please Login');
            return redirect('/login');

        }
    }
    
}
