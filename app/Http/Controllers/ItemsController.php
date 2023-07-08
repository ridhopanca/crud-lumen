<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ItemsController extends Controller
{
    public function list(Request $request){
        try {
            $dataRequest = json_decode($request->getContent(), true) ? json_decode($request->getContent(), true) : [];
            $limit = isset($dataRequest['limit']) ? $dataRequest['limit'] : '';
            $search = isset($dataRequest['search']) ? $dataRequest['search'] : '';
            $items = Item::when($search != '', function($q) use($search) {
                        return $q->where('name','like','%'.trim($search).'%');
                    });
            if($limit != ''){
                $items = $items->orderBy('name')->paginate($limit);
            } else {
                $items = $items->orderBy('name')->get()->toArray();
            }

            if(sizeof($items) < 1){
                $responseData = [
                    'response_code' => 404,
                    'response_desc' => 'Data not found',
                    'response_data' => []
                ];
                return response()->json($responseData,404);
            }

            $responseData = [
                'response_code' => 200,
                'response_desc' => '',
                'response_data' => $items
            ];
            return response()->json($responseData);
        } catch(\Exception $e){
            $responseData = [
                'response_code' => 500,
                'response_desc' => 'Internal Server Error',
                'response_error' => $e->getMessage().' - '.$e->getLine()
            ];
            return response()->json($responseData,500);
        }
    }

    public function save(Request $request){
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['required','unique:items'],
                'image' => 'required',
                'sell_price' => 'required',
                'buy_price' => 'required',
                'quantity'=> 'required'
            ],[
                'name.required' => 'Name field is required',
                'image.required' => 'Image field is required',
                'sell_price.required' => 'Sell price field is required',
                'buy_price.required' => 'Buy price field is required',
                'quantity.required' => 'Quantity field is required',
                'image.required' => 'Image field is required',
            ]);
            if($validator->fails()){
                return $this->failedValidation(422, 'Unprocessable Requests', $validator->errors());
            }
            $sellPrice = str_replace(',','',$request->sell_price);
            $buyPrice = str_replace(',','',$request->buy_price);
            $quantity = str_replace(',','',$request->quantity);

            $items = new Item();
            $items->name = trim($request->name);
            $items->sell_price = floatval(trim($sellPrice));
            $items->buy_price = floatval(trim($buyPrice));
            $items->quantity = floatval(trim($quantity));
            if($request->file('image')){
                $file = $request->file('image');
                $md5file = md5_file($file->getRealPath());
                $extension = $file->guessExtension();
                $customFilename = $md5file.'.'.$extension;
                $file->move('images',$customFilename);
                $items->images = $request->root().'/images/'.$customFilename;
            }
            $items->save();
            $responseData = [
                'response_code' => 200,
                'response_desc' => 'Success',
                'response_data' => $items
            ];
            DB::commit();
            return response()->json($responseData);
        }  catch(\Exception $e){
            DB::rollBack();
            $responseData = [
                'response_code' => 500,
                'response_desc' => 'Internal Server Error',
                'response_error' => $e->getMessage().' - '.$e->getLine()
            ];
            return response()->json($responseData,500);
        }
    }

    public function show($id){
        try {
            $param = json_decode(base64_decode($id));
            $id = isset($param->id) ? $param->id : 0;
            $item = Item::find($id);
            if(!$item){
                $responseData = [
                    'response_code' => 404,
                    'response_desc' => 'Data not found',
                    'response_data' => (object)[]
                ];
                return response()->json($responseData,404);
            }
            $responseData = [
                'response_code' => 200,
                'response_desc' => '',
                'response_data' => $item
            ];
            return response()->json($responseData);
        } catch(\Exception $e){
            $responseData = [
                'response_code' => 500,
                'response_desc' => 'Internal Server Error',
                'response_error' => $e->getMessage().' - '.$e->getLine()
            ];
            return response()->json($responseData,500);
        }

    }

    public function update(Request $request){
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required',
                'name' => ['required','unique:items,id,'.$request->id],
                'image' => 'required',
                'sell_price' => 'required',
                'buy_price' => 'required',
                'quantity'=> 'required'
            ],[
                'id' => 'required',
                'name.required' => 'Name field is required',
                'image.required' => 'Image field is required',
                'sell_price.required' => 'Sell price field is required',
                'buy_price.required' => 'Buy price field is required',
                'quantity.required' => 'Quantity field is required',
                'image.required' => 'Image field is required',
            ]);
            if($validator->fails()){
                return $this->failedValidation(422, 'Unprocessable Requests', $validator->errors());
            }
            $items = Item::find($request->id);
            if(!$items){
                $responseData = [
                    'response_code' => 404,
                    'response_desc' => 'Data not found',
                    'response_data' => (object)[]
                ];
                return response()->json($responseData,404);
            }
            $sellPrice = str_replace(',','',$request->sell_price);
            $buyPrice = str_replace(',','',$request->buy_price);
            $quantity = str_replace(',','',$request->quantity);
            $items->name = trim($request->name);
            $items->sell_price = floatval(trim($sellPrice));
            $items->buy_price = floatval(trim($buyPrice));
            $items->quantity = floatval(trim($quantity));
            if($request->file('image')){
                $file = $request->file('image');
                $md5file = md5_file($file->getRealPath());
                $extension = $file->guessExtension();
                $customFilename = $md5file.'.'.$extension;
                $file->move('images',$customFilename);
                $items->images = $request->root().'/images/'.$customFilename;
            }
            $items->save();
            $responseData = [
                'response_code' => 200,
                'response_desc' => 'Success',
                'response_data' => $items
            ];
            DB::commit();
            return response()->json($responseData);
        }  catch(\Exception $e){
            DB::rollBack();
            $responseData = [
                'response_code' => 500,
                'response_desc' => 'Internal Server Error',
                'response_error' => $e->getMessage().' - '.$e->getLine()
            ];
            return response()->json($responseData,500);
        }
    }

    public function delete(Request $request){
        $now  = Carbon::now();
        $dataRequest = json_decode($request->getContent(), true) ? json_decode($request->getContent(), true) : [];
        $id = isset($dataRequest['id']) ? $dataRequest['id'] : '';
        try {
            if(is_array($id)){
                if(sizeof($id) > 0){
                    DB::beginTransaction();
                    try {
                        foreach($id as $cd){
                            $deleteData = Item::find($cd);
                            if($deleteData){
                                $deleteData->delete();
                            }
                        }
                        DB::commit();
                        return response()->json([
                            'response_code' => 200,
                            'response_desc' => "Success"
                        ], 200);
                    } catch(\Exception $e){
                        DB::rollBack();
                        $responseData = [
                            'response_code' => 500,
                            'response_desc' => 'Internal Server Error'
                        ];
                        return response()->json($responseData, 500);
                    }
                } else {
                    $responseData = [
                        'response_code' => 404,
                        'response_desc' => 'Data not found'
                    ];
                    return response()->json($responseData, 404);
                }
            } else {
                $item = Item::find($id);
                if(!$item){
                    $responseData = [
                        'response_code' => 404,
                        'response_desc' => 'Data not found'
                    ];
                    return response()->json($responseData,404);
                }
                $item->delete();
                return response()->json([
                    'response_code' => 200,
                    'response_desc' => "Success"
                ], 200);
            }
        } catch(\Exception $e){
            $responseData = [
                'response_code' => 500,
                'response_desc' => 'Internal Server Error',
                'response_error' => $e->getMessage().' - '.$e->getLine()
            ];
            return response()->json($responseData,500);
        }
    }
}
