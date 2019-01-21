<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
Use Image;

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
        //$this->authorize('isAdmin');
        //return DB::select('SELECT * FROM users ORDER BY name ASC');

        if(\Gate::allows('isAdmin') || \Gate::allows('isAuthor')){
            return User::latest()->paginate(5);
        }
        
        
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

        $this->validate($request, [
            'name' => 'required|string|max:50',
            'email' => 'required|string|email|max:50|unique:users,email,'.$user->id,
            'password' => 'sometimes|required|min:6'
        ]);

        $currentPhoto = $user->photo;
        if($request->photo != $currentPhoto){
            $name = time().'.'.explode('/', explode(':',substr($request->photo, 0,strpos
            ($request->photo, ';')))[1])[1];
            
            \Image::make($request->photo)->save(public_path('img/profile/').$name);

            $request->merge(['photo' => $name]);

            $userPhoto = public_path('img/profile/').$currentPhoto;

            if(file_exists($userPhoto)){
                @unlink($userPhoto);
            }
        }

        if(!empty($request->password)){
            $request->merge(['password' => Hash::make($request['password'])]);
        }

        $user->update($request->all());
        return ["message" => "Success"];

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

        $password = Hash::make($request->password);

        $users = DB::update('
           UPDATE users SET
           name = "'.$request->name.'",
           email = "'.$request->email.'",
           password = "'.$password.'",
           type = "'.$request->type.'",
           bio = "'.$request->bio.'",
           photo = "'.$request->photo.'",
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

        $this->authorize('isAdmin');
        $user = User::findOrFail($id);

        $user->delete();

        return ["message" => "User Deleted"];
    }

    public function search(){
        if($search = \Request::get('q')){
            $users = User::where(function($query) use ($search){
                $query->where('name','LIKE','%'.$search.'%')->orWhere('email','LIKE','%'.$search.'%');
            })->paginate(5);
        }else{
            $users = User::latest()->paginate(5);
        }
        
        // if($search = \Request::get('q')){
        //     $users = DB::select('SELECT * FROM users WHERE name LIKE "%'.$search.'%" OR email LIKE "'.$search.'"');
        // }
        
        return $users;

    }
}
