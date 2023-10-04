<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Requests\UserStoreRequest;
use App\Mail\WelcomeEmail;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use SimpleXMLElement;

class RandomUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(UserRequest $request)
    {
        try {
            // declarations
            $fields = [
                'name',
                'surname',
                'email',
                'phone',
                'country'
            ];
            $field = $request->field ?: 'surname'; # if field is empty set surname
            $orderBy = $request->orderBy ?: 'desc'; # if not asc set desc
            $type = $request->type ?: 'xml'; # if empty type set xml

            // sort users
            $sql = User::orderBy($field, $orderBy);

            // get users
            $users = $sql->paginate($request->limit, ['*'], 'page', $request->page);

            // if user want to get users in json
            if ($type == 'json') {
                $answer['paginate']['currentPage'] = $users->currentPage();
                $answer['paginate']['lastPage'] = $users->lastPage();
                $answer['paginate']['totalUsers'] = $users->total();
                $answer['users'] = $users->items();
                return response()->json($answer, 200);
            }

            // create a root XML element
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><root></root>');

            // paginate section
            $xmlPaginate = $xml->addChild('paginate');
            $xmlPaginate->addChild('currentPage', $users->currentPage());
            $xmlPaginate->addChild('lastPage', $users->lastPage());
            $xmlPaginate->addChild('totalUsers', $users->total());

            // add users section
            $xmlUsers = $xml->addChild('users');

            foreach ($users as $user) {
                $xmlUser = $xmlUsers->addChild('user');
                $xmlUser->addChild('name', $user->name);
                $xmlUser->addChild('surname', $user->surname);
                $xmlUser->addChild('email', $user->email);
                $xmlUser->addChild('phone', $user->phone);
                $xmlUser->addChild('country', $user->country);
            }
            return response($xml->asXML())->header('Content-Type', 'application/xml');
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 403);
        }
    }

    /**
     * Store a newly created resource.
     */
    public function store(UserStoreRequest $request)
    {
        try {
            // declarations
            $limit = ($request->limit) ?: 10;
            $users = [];

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
