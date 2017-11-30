<?php

namespace App\Http\Controllers;

use App\User;
use Bican\Roles\Models\Role;
use Gate;
use Illuminate\Http\Request;
use Validator;

class UsersController extends Controller
{
    protected $_redirectPath = '/users';

    public function listUsers()
    {
        if (Gate::denies('read', User::class)) {
            abort('403');
        }

        return view('users.index', ['users' => User::all()]);
    }

    public function create()
    {
        if (Gate::denies('create', User::class)) {
            abort(403);
        }
        return view('users.create');
    }

    public function user($userId)
    {
        $user = User::findOrFail($userId);
//        print_r($user);
        return view('users.update', ['user' => $user]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Foundation\Validation\ValidationException
     */
    public function postCreate(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            $this->throwValidationException(
                $request, $validator
            );
        }

        $this->createUser($request->all());

        return redirect($this->_redirectPath);
    }

    public function updateUser(Request $request, $userId)
    {
//        todo
        $updateFields = ['id', 'name', 'email', 'old_password', 'password', 'password_confirmation'];
        $newData = $request->only($updateFields);
        $validator = Validator::make($newData, [
            'id' => 'required',
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $userId,
            'password' => 'sometimes|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return redirect('users/' . $userId)
                ->withErrors($validator)
                ->withInput();
        }

        /** @var User $user */
        $user = User::findOrFail($userId);
        if ((int)$user->id !== (int)$newData['id']) {
            $validator->errors()->all('id', 'User Id mismatch');
            return redirect('users/' . $userId)
                ->withErrors($validator)
                ->withInput();
        }

        $user->fill($newData)->save();
        return redirect('users');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     * @return User
     */
    protected function createUser(array $data)
    {
        $user = User::create(
            [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
            ]
        );
        $role = $this->getRole($data);
        $user->attachRole($role);
        return $user;
    }

    /**
     * @return Role
     */
    protected function getRole(array $data = [])
    {
        $slug = 'apiuser';
        if (isset($data['role'])) {
            $slug = $data['role'];
        }
        return Role::where('slug', $slug)->first();
    }
}
