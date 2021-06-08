<?php

namespace App\Http\Controllers;


use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateInfoRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{

    /**
     * Create an user, hash the password and return the user
     * @param string $first_name
     * @param string $last_name
     * @param string $email
     * @param string $password
     *
     *@return $user
     */
    public function register(RegisterRequest $request)
    {
        $user = User::create(
            $request->only('first_name', 'last_name', 'email')
                + [
                    'password' => Hash::make($request->input('password')),
                    'is_admin' => $request->path() === 'api/admin/register' ? 1 : 0,
                ]
        );

        return response($user, Response::HTTP_CREATED);
    }

    /**
     * LOGIN USER ->
     * Verify if the request contain email and password, if is ok take the user logged
     * and create a jwt token for it
     *
     * @return token
     */
    public function login(Request $request)
    {
        if (!\Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return response([
                'error' => 'Invalid credentials'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = \Auth::user();

        $adminLogin = $request->path() === 'api/admin/login';

        //ambassador not can logging into admin pannel
        if ($adminLogin && !$user->is_admin) {
            return response([
                'error' => 'Access Denied!'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $scope = $adminLogin ? 'admin' : 'ambassador';
        $jwt = $user->createToken('token', [$scope])->plainTextToken;

        $cookie = cookie('jwt', $jwt, 60 * 24); // 1 day

        return response([
            'message' => 'success'
        ])->withCookie($cookie);
    }

    /**
     * Authenticated user
     *
     * @return $user
     */
    public function user(Request $request)
    {
        return $request->user();
    }

    /**
     * Logout user by deleted cookie
     */
    public function logout()
    {
        $cookie = \Cookie::forget('jwt');

        return response([
            'message' => 'success'
        ])->withCookie($cookie);
    }

    /**
     * Update user data
     */
    public function updateInfo(UpdateInfoRequest $request)
    {
        $user = $request->user();
        $user->update($request->only('first_name', 'last_name', 'email'));

        return response($user, Response::HTTP_ACCEPTED);
    }

    /**
     * Update user password
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = $request->user();
        $user->update([
            'password' => \Hash::make($request->input('password'))
        ]);

        return response($user, Response::HTTP_ACCEPTED);
    }
}
