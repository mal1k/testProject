<?php

namespace App\Http\Controllers;

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
    public function index(Request $request)
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
            $field = (strtolower($request->field)) ?: 'surname'; # if field is empty set surname
            $orderBy = (strtolower($request->orderBy) == 'asc') ? 'asc' : 'desc'; # if not asc set desc
            $type = (strtolower($request->type) == 'json') ? 'json' : 'xml'; # if not asc set desc
            $limit = ($request->limit);
            $page = ($request->page) ?: 1;
            $skip = ($page-1) * $limit; # skip elements

            // errors
            if ( !in_array($field, $fields) ) # if field not exist
                throw new Exception("This field does not exist", 403);
            if ($limit < 0)
                throw new Exception("Limit cannot be less than 1", 403);
            if (!is_int($limit))
                throw new Exception("Limit need to be integer", 403);

            // sort users
            $sql = User::orderBy($field, $orderBy);

            // get users
            $users = $sql->paginate($limit, ['*'], 'page', $page);

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
    public function store(Request $request)
    {
        try {
            // declarations
            $limit = ($request->limit) ?: 10;
            $users = [];

            // errors
            if ($limit < 0)
                throw new Exception("Limit cannot be less than 1", 403);
            if (!is_int($limit))
                throw new Exception("Limit need to be integer", 403);

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
