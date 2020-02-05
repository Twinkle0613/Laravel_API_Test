<?php

namespace App\Http\Controllers;

use Gate;
use App\User;
use Validator;
use File;
use App\Imports\UserImport;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\ShowUserRequest;
use App\Http\Requests\IndexUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\ImportUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public $fails = [];
    public $successes = [];
     
    public function index(IndexUserRequest $request)
    {
        if(!empty($request->search)){
            $searchFields = ['name','email','created_at','updated_at'];
            $user = User::where(function($query) use($request, $searchFields){
                $searchWildcard = '%' . $request->search . '%';
                foreach($searchFields as $field){
                    $query->orWhere($field, 'LIKE', $searchWildcard);
                }
            })->paginate($request->per_page,['*'], 'page', $request->page_number);;
        }else{
            $user  = User::paginate($request->per_page,['*'], 'page', $request->page_number);
        }

        return (new UserResource($user))->response();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request) //Validation is handled in StoreUserRequest
    {
        $data = $request->all();
        $data['password'] = bcrypt($data['password']);
        return (new UserResource(User::create($data)))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id,ShowUserRequest $request)
    {
        return (new UserResource(User::find($id)))->response();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id,UpdateUserRequest $request)
    {
        $user = User::find($id);
        $data = $request->all();
        if(isset($data['password'])){
            $data['password'] = bcrypt($data['password']);
        }else{
            unset($data['password']);
        }
        $user->update($data);
        
        return (new UserResource($user))->response();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id,ShowUserRequest $request)
    {
        User::find($id)->delete();
        return response(null,Response::HTTP_NO_CONTENT);
    }

    public function import(ImportUserRequest $request){
        $file = $request->file('file');

        if(strtolower($file->getClientOriginalExtension()) == 'txt'){
            $import_data = $this->covertTextFileToArray($file);
        }else{
            $import_data = Excel::toArray(new UserImport, $file)[0];
        }

        $read_user_ids = $this->processImportData($import_data);
        
        $response = [];
        $response['data'] = User::whereIn('id',$read_user_ids)->get();
        $response['fails'] = $this->fails;
        return response()->json($response);
    }
    

    public function processImportData($import_data){
        $read_ids = [];
        if (!empty($import_data)) {
            $param_name = $import_data[0];
            unset($import_data[0]); // remove first row
            if (!empty($import_data)) {
                foreach ($import_data as $count => $row) {
                    unset($data);
                    $data = [];
                    $data = $this->renameArray($row, $param_name);
                    if (!empty($data) && isset($data['method'])) {
                        $validation = Validator::make($data, User::getRules($data['method']));
                        if (!$validation->fails()) {
                            switch(strtolower($data['method']) ){
                                case 'create':
                                    User::create($data);
                                break;
                                case 'update':
                                    $validation = Validator::make($data, User::getRules($data['method'],$data['id']));
                                    if (!$validation->fails()) {
                                        $user = User::find($data['id']);
                                        if(isset($data['password'])){
                                            $data['password'] = bcrypt($data['password']);
                                        }else{
                                            unset($data['password']);
                                        }
                                        if( !User::where('email',$data['email'])->exists()){
                                            $user->update($data);
                                        }
                                    }else{
                                        $this->fails[] = [$count => $validation->errors()];
                                    }
                                break;
                                case 'read':
                                    $read_ids[] = $data['id'];
                                break;
                                case 'delete':
                                    $user = User::find($data['id']);
                                    $user->delete();
                                break;
                            }
                        }else{
                            $this->fails[] = [$count => $validation->errors()];
                        }
                    }
                }
            }
         }
         return $read_ids;
    }

    public function renameArray($row,$param_name){

        foreach ($row as $key => $value) {
            $name = isset($param_name[$key]) ? $param_name[$key] : '';
            if (!empty($name)) {
                $data[$name] = $value;
            }
        }
        return $data;
    }

    public function covertTextFileToArray($file){
        $fopen = fopen($file, "r");

        $fread = fread($fopen,filesize($file->getRealPath()));

        fclose($fopen);
        $remove = "\n";

        $split = explode($remove, $fread);

        $array = [];
        foreach ($split as $string)
        {
            $row = explode(',', str_replace("\r","",$string) );

            if($row[0]){
                array_push($array,$row);
            }
            
        }
        return $array;
    }
}


        
