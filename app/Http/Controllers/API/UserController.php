<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{



    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //return DB::select('SELECT * FROM users ORDER BY name ASC');
        return User::all();
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $this->validate($request, [
            'name' => 'required|string|max:50',
            'email' => 'required|string|email|max:50|unique:users',
            'password' => 'required|string|min:6'
        ]);

        // return User::create([
        //     'name' => $request['name'],
        //     'email' => $request['email'],
        //     'type' => $request['type'],
        //     'bio' => $request['bio'],
        //     'photo' => $request['photo'],
        //     'password' => Hash::make($request['password'])
        // ]);

        $users = DB::insert('
            INSERT INTO users (name,email,password,type,bio,photo,created_at,updated_at) VALUES
            ("'.$request['name'].'","'.$request['email'].'","'.Hash::make($request['password']).'",
            "'.$request['type'].'","'.$request['bio'].'","'.$request['photo'].'",now(),now())
        
        ');
        if ($users){
            return 'good';
        }
        else{
            return 'failed';
        }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show_profile()
    {
        return auth('api')->user();
    }

    public function update_profile(Request $request)
    {
        $user = auth('api')->user();

        return $request->photo;
        //return ["message" => "Update information successfully"];
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $user = User::findOrFail($id);

        $this->validate($request, [
            'name' => 'required|string|max:50',
            'email' => 'required|string|email|max:50|unique:users,email,'.$user->id,
            'password' => 'sometimes|min:6'
        ]);

        $users = DB::update('
           UPDATE users SET
           name = "'.$request->name.'",
           email = "'.$request->email.'",
           password = "'.Hash::make($request['password']).'",
           type = "'.$request->type.'",
           bio = "'.$request->bio.'",
           photo = "'.$request->bio.'",
           updated_at = now()
           WHERE id = "'.$id.'"
        
        ');

        //$user->update($request->all());
        
        return ["message" => "Updated successfuly"];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $user = User::findOrFail($id);

        $user->delete();

        return ["message" => "User Deleted"];
    }
}
