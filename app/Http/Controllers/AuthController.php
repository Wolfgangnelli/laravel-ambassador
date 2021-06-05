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
        if ($request->has(['first_name', 'last_name', 'email'])) {
            $user = User::create(
                $request->only('first_name', 'last_name', 'email')
                    + [
                        'password' => Hash::make($request->input('password')),
                        'is_admin' => 1,
                    ]
            );
        }

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
        $jwt = $user->createToken('token', ['admin'])->plainTextToken;

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
