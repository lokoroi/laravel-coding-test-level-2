<?php

namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Projects;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Exception;

class ProjectController extends Controller
{
    /**
     * @OA\Get(
     * path="/v1/project",
     * summary="Projects",
     * operationId="getProjects",
     * tags={"Projects"},
     * security={ {"Bearer": {} }},
     * 
     * @OA\Parameter(
     *      description="Search By Name",
     *      in="query",
     *      name="q",
     *      required=false,
     *      example="",
     *      @OA\Schema(
     *          type="string",
     *          format="text"
     *      )
     * ),
     * 
     * @OA\Parameter(
     *      description="Page Index (default 0)",
     *      in="query",
     *      name="pageIndex",
     *      required=false,
     *      example="",
     *      @OA\Schema(
     *          type="integer",
     *          format="int64"
     *      )
     * ),
     * 
     * @OA\Parameter(
     *      description="Page Size (default 3)",
     *      in="query",
     *      name="pageSize",
     *      required=false,
     *      example="",
     *      @OA\Schema(
     *          type="integer",
     *          format="int64"
     *      )
     * ),
     * 
     * @OA\Parameter(
     *      description="Sort By (default by name)",
     *      in="query",
     *      name="sortBy",
     *      required=false,
     *      example="",
     *      @OA\Schema(
     *          type="string",
     *          format="text"
     *      )
     * ),
     * 
     * @OA\Parameter(
     *      description="Sort Direction (default ASC)",
     *      in="query",
     *      name="sortDirection",
     *      required=false,
     *      example="",
     *      @OA\Schema(
     *          type="string",
     *          format="text"
     *      )
     * ),
     * 
     *   @OA\Response(
     *        response=200,
     *        description="Successful operation",
     *     ),
     *  @OA\Response(
     *        response=400,
     *        description="Bad Request",
     *    ),
     *    @OA\Response(
     *        response=401,
     *        description="Unauthorized",
     *    ),
     *    @OA\Response(
     *        response=403,
     *        description="Forbidden"
     *    ),
     *    @OA\Response(
     *        response=500,
     *        description="Internal Server Error",
     *    )
     * )
    */
    public function index(Request $request)
    {
        try {
            $credentials = $request->all();
            $validator = Validator::make($credentials, [
                'q' => 'string|max:100',
                'pageIndex' => 'integer',
                'pageSize' => 'integer',
                'sortBy' => 'string|max:100',
                'sortDirection' => 'string|max:4',
            ]);

            # Cred Validation
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors(), 'status' => Response::HTTP_BAD_REQUEST], Response::HTTP_BAD_REQUEST);
            }
            
            # Set Limit Data
            $limit = $request->pageSize?$request->pageSize:3;

            # Set Page Index (Start Record)
            $offset = $request->pageIndex?$request->pageIndex:0;

            # Set sort data
            $sortBy = $request->sortBy?$request->sortBy:'name';
            $sortDirection = $request->sortDirection?$request->sortDirection:'ASC';
           
            # Query Data
            $data = new Projects;
            if ($request->q) {
                $data = $data->where('name','LIKE','%'.$request->q.'%');
            }

            $data = $data->offset($offset)
                    ->limit($limit)
                    ->orderBy($sortBy,$sortDirection)
                    ->get();

            $count = $data?count($data):0;
            return response()->json([
                'status' => Response::HTTP_OK,
                'total' => $count,
                'data' => $data
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
   
    /**
     * @OA\Post(
     * path="/v1/project",
     * summary="Create Projects",
     * operationId="storeProjects",
     * tags={"Projects"},
     * security={ {"Bearer": {} }},
     * @OA\RequestBody(
     *      required=true,
     *      description="Create Project",
     *      @OA\JsonContent(
     *          @OA\Schema(title = "Json Create Project"),
     *          example = {
                    "name": "Second Project",
     *          }
     *      ),
     * ),
     *   @OA\Response(
     *        response=200,
     *        description="Successful operation",
     *     ),
     *  @OA\Response(
     *        response=400,
     *        description="Bad Request",
     *    ),
     *    @OA\Response(
     *        response=401,
     *        description="Unauthorized",
     *    ),
     *    @OA\Response(
     *        response=403,
     *        description="Forbidden"
     *    ),
     *    @OA\Response(
     *        response=500,
     *        description="Internal Server Error",
     *    )
     * )
    */
    public function store(Request $request)
    {
        try {
            $credentials = $request->only('name');
            $validator = Validator::make($credentials, [
                'name' => 'required|string|unique:projects|max:150',
            ]);

            # Cred Validation
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors(), 'status' => Response::HTTP_BAD_REQUEST], Response::HTTP_BAD_REQUEST);
            }

            $data = Projects::create(["name" => $request->name]);

            return response()->json([
                'status' => Response::HTTP_CREATED,
                'message' => "Create Project Success",
                'data' => $data
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Get(
     * path="/v1/project/{id}",
     * summary="Show Projects",
     * operationId="showProjects",
     * tags={"Projects"},
     * security={ {"Bearer": {} }},
     * 
     * @OA\Parameter(
     *      description="id",
     *      in="path",
     *      name="id",
     *      required=true,
     *      example="",
     *      @OA\Schema(
     *          type="string",
     *          format="text"
     *      )
     * ),
     *   @OA\Response(
     *        response=200,
     *        description="Successful operation",
     *     ),
     *  @OA\Response(
     *        response=400,
     *        description="Bad Request",
     *    ),
     *    @OA\Response(
     *        response=401,
     *        description="Unauthorized",
     *    ),
     *    @OA\Response(
     *        response=403,
     *        description="Forbidden"
     *    ),
     *    @OA\Response(
     *        response=500,
     *        description="Internal Server Error",
     *    )
     * )
    */
    public function show($id)
    {
        try {
            $data = Projects::find($id);
            if ($data) {
                return response()->json([
                    'status' => Response::HTTP_OK,
                    'data' => $data
                ], Response::HTTP_OK);
            }

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => "Data Not Found"
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Patch(
     * path="/v1/project/{id}",
     * summary="Update Projects",
     * operationId="updateProjects",
     * tags={"Projects"},
     * security={ {"Bearer": {} }},
     * @OA\Parameter(
     *      description="id",
     *      in="path",
     *      name="id",
     *      required=true,
     *      example="",
     *      @OA\Schema(
     *          type="string",
     *          format="text"
     *      )
     * ),
     * @OA\RequestBody(
     *      required=true,
     *      description="Update Project",
     *      @OA\JsonContent(
     *          @OA\Schema(title = "Json Update Project"),
     *          example = {
                    "name": "Update Project",
     *          }
     *      ),
     * ),
     *   @OA\Response(
     *        response=200,
     *        description="Successful operation",
     *     ),
     *  @OA\Response(
     *        response=400,
     *        description="Bad Request",
     *    ),
     *    @OA\Response(
     *        response=401,
     *        description="Unauthorized",
     *    ),
     *    @OA\Response(
     *        response=403,
     *        description="Forbidden"
     *    ),
     *    @OA\Response(
     *        response=500,
     *        description="Internal Server Error",
     *    )
     * )
    */
    public function projectUpdate(Request $request, $id)
    {
        try {
            $data = Projects::find($id);
            if ($data) {
                $credentials = $request->only('name');
                $validator = Validator::make($credentials, [
                    'name'  => 'required|max:100|unique:projects',
                ]);

                # Cred Validation
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors(), 'status' => Response::HTTP_BAD_REQUEST], Response::HTTP_BAD_REQUEST);
                }

                $data->name = $request->name;
                $data->save();

                return response()->json([
                    'status' => Response::HTTP_OK,
                    'data' => "Update Project Success"
                ], Response::HTTP_OK);
            }
            
            return response()->json([
                'status' => Response:: HTTP_OK,
                'message' => "Data Not Found"
            ], Response:: HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Delete(
     * path="/v1/project/{id}",
     * summary="Delete Project By Id",
     * operationId="deleteProjectId",
     * tags={"Projects"},
     * security={ {"Bearer": {} }},
     * 
     * @OA\Parameter(
     *      description="id",
     *      in="path",
     *      name="id",
     *      required=true,
     *      example="",
     *      @OA\Schema(
     *          type="string",
     *          format="text"
     *      )
     * ),
     *   @OA\Response(
     *        response=200,
     *        description="Successful operation",
     *     ),
     *  @OA\Response(
     *        response=400,
     *        description="Bad Request",
     *    ),
     *    @OA\Response(
     *        response=401,
     *        description="Unauthorized",
     *    ),
     *    @OA\Response(
     *        response=403,
     *        description="Forbidden"
     *    ),
     *    @OA\Response(
     *        response=500,
     *        description="Internal Server Error",
     *    )
     * )
    */
    public function destroy($id)
    {
        try {
            $data = Projects::find($id);

            if ($data) {
                $data->delete();
                return response()->json([
                    'status' => Response::HTTP_OK,
                    'message' => "delete project success"
                ], Response::HTTP_OK);
            }
            
            return response()->json([
                'status' => Response:: HTTP_OK,
                'message' => "Data Not Found"
            ], Response:: HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
