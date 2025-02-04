<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Repositories\TipoSolicitanteRepository;
use App\Validators\UserValidator;
use App\Repositories\DemandRepository;
use App\Repositories\UserRepository;
use App\Services\UserService;
use App\Entities\TipoSolicitante;
use App\Entities\User;
use Illuminate\Support\Facades\Gate;
Use App\Services\DemandService;
use App\Entities\Demand;
Use App\Repositories\GenericFunctionsForRepository;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ProductionsController;
use App\Services\AttachmentService;
use PhpParser\Node\Expr\Empty_;

/**
 * Class DemandController.
 *
 * @package namespace App\Http\Controllers;
 */

class DemandsController extends Controller
{
    /**
     * @var GenericFunctionsForRepository
     */
    protected $repositoryFunctions;

    /**
     * @var DemandRepository
     */
    protected $repository;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var DemandValidator
     */
    protected $validator;
    protected $service;

    /**
     * @var AttachmentService
     */
    protected $attachmentService;

    /**
     * DemandssController constructor.
     *
     * @param DemandRepository $repository
     * @param UserRepository $userRepository
     */
    public function __construct(DemandRepository $repository, DemandService $service, UserRepository $userRepository,
    AttachmentService $attachmentService
    )
    {
        $this->repository = $repository;
        $this->userRepository = $userRepository;
        $this->service = $service;
        $this->attachmentService = $attachmentService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function register(){

        if(Gate::allows('requester')){

            $usersAssisted = $this->userRepository->findwhere(['permission'=>'1']);

            $usersAssistedList = User::where(['permission'=>'1'])->pluck('name','id');

            return view('demands.demandAdd',[
                'usersAssisted' => $usersAssisted,
                'usersAssistedList' => $usersAssistedList
            ]);
        }
        else{return view('accessDenied');}
        
    }

    public function startProduction(){
        
        if(Gate::allows('prod')){

           $start = $this->service->startProduction($_POST["id"]);

           return redirect()->route('demand.index');
        }

        else{return view('accessDenied');}

    }

    public function show(Request $id){
        if(Gate::allows('admReqProd')){

            $demand = $this->repository->all()->find($id['demand']);

            $extensao = substr($demand->filename, strpos($demand->filename, '.')+1 );

            if($demand!=null){
                return redirect()->route('show',[
                    'demand'    => $demand,
                ]);
            }
        }
        else{return view('accessDenied');}

    }

    public function index(){

        if(Gate::allows('admReqProd')){

            if(auth()->user()->permission==2){

                $demands = $this->repository->findwhere(['requester_id'=>auth()->user()->id]);

                if($demands!=null){
                    return view('demands.index',[
                        'demands' => $demands,
                    ]);
                }
                else{
                    return view('demands.index');
                }

            }
            
            else{

                $demands = $this->repository->all();

                if($demands!=null){
                    return view('demands.index',[
                        'demands' => $demands,
                    ]);
                }
                else{
                    return view('demands.index');
                }
            }
        }
        else{return view('accessDenied');}

    }

    public function removeds(){

        if(Gate::allows('admOrReq')){
            
            $demands =  Demand::onlyTrashed()->get();
            
            if($demands!=null){}

            return view('demands.removed',[
                'demands' => $demands,
            ]);

            dd($demands);
        }
        else{return view('accessDenied');}

    }

    public function recover()
    {
        if(Gate::allows('admOrReq')){

            $demand =  Demand::onlyTrashed()->where('id', $_POST["id"])->restore();

            return redirect()->route('demandRemoved');
        }
        else{return view('accessDenied');}
    }

    /**
     * Store a newly created resource in storage.
     *
     * 
     *
     * @param  Request $requestt
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */

    public function store(Request $requestPar)
    {

        if(Gate::allows('requester')){

            $requeststore = $this->service->store($requestPar->all(),auth()->user()->id);

            $all = $this->repository->all();

            $files = $requestPar->file('arquivo');

            foreach ($all as $key => $value) {$last = $value;}

            if(!empty($requestPar->file('arquivo'))){
                foreach ($files as $file) {

                    $file->storeAs('public/demands',$last['id'].'.'.$file->getClientOriginalExtension());
                    $filename = ["filename"=>"storage/demands".'/'.$last['id'].".".$file->getClientOriginalExtension()];

                    $data = [
                        'name'=>$file->getClientOriginalName(),
                        'original_name'=>$file->getClientOriginalName(),
                        'file'=>$filename['filename'],
                        'owner_id'=>$last['id'],
                        'owner_type'=>'App\\Entities\\Demand'
                    ];
                    
                    $request = $this->attachmentService->store($data);
                }
            }
            
            session()->flash('success',[
                'success'      => $requeststore['success'],
                'messages'     => $requeststore['message'],
            ]);
            
            $usersAssistedList = User::where(['permission'=>'1'])->pluck('name','id');

            return view('demands.demandAdd',[
                'usersAssistedList' => $usersAssistedList
            ]);
        }
        else{return view('accessDenied');}

    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */


    public function edit(Request $id)
    {
        $demand = $this->repository->find($id["id"]);

        $usersAssisted = $this->userRepository->findwhere(['permission'=>'1']);

        $usersAssistedList = User::where(['permission'=>'1'])->pluck('name','id');

        return view('demands.demandEdit',[
            'demand' => $demand,
            'usersAssisted' => $usersAssisted,
            'usersAssistedList' => $usersAssistedList]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  DemandUpdateRequest $request
     * @param  string            $id
     *
     * @return Response
     *
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */


    public function update(Request $requestPar, $id)
    {

        $request = $this->service->update($requestPar->all(),$id);

        session()->flash('success',[
            'success'      => $request['success'],
            'messages'     => $request['message']
        ]);

        return redirect()->route('demand.index');

    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */


    public function destroy($id)
    {

        if(Gate::allows('admOrReq')){
            $request = $this->service->destroy($id);

            session()->flash('success',[
                'success'      => $request['success'],
                'messages'     => $request['message']
            ]);

            return redirect()->route('demand.index');
        }
        else{return view('accessDenied');}

        
    }
}


