<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeEmail;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class RandomUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // declarations
        $fields = [
            'name',
            'surname',
            'email',
            'phone',
            'country'
        ];
        $field = (strtolower($request->field)) ?: 'surname'; # if field is empty set surname
        $orderBy = (strtolower($request->orderBy) == 'asc') ? 'asc' : 'desc'; # if not asc set desc

        // errors
        if ( !in_array($field, $fields) ) # if field not exist
            throw new Exception("This field does not exist", 403);

        // get and sort users
        $users = User::orderBy('surname', $orderBy)->get();

        return response()->json($users, 200);
    }

    /**
     * Store a newly created resource.
     */
    public function store(Request $request)
    {
        try {
            // declarations
            $limit = ($request->limit) ?: 10;
            $users = [];

            // errors
            if ($limit < 0)
                throw new Exception("Limit cannot be less than 1", 403);

            // get users data
            for ($i = 0; $i < $limit; $i++) {
                $response = Http::get('https://randomuser.me/api/');
                $userData = $response->json()['results'][0];
                $data[] = $userData;
            }

            // loop for users data
            foreach ($data as $key => $userData) {
                $user = User::create([
                    'name' => $userData['name']['first'],
                    'surname' => $userData['name']['last'],
                    'email' => $userData['email'],
                    'phone' => $userData['phone'],
                    'country' => $userData['location']['country'],
                    'password' => $userData['login']['password'],
                ]);

                Mail::to($user->email)->send(new WelcomeEmail($user, $userData['login']['password']));

                $users[] = $user;
            }

        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 406);
        }

        return response()->json($users);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
