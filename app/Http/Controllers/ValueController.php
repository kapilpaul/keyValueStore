<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ValueController extends Controller
{
    private $ttl;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->ttl = env('TTL');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $allKeys = isset($request->keys) ? explode(',', $request->keys) : Cache::get('allKeys');

        $allData = [];
        if($allKeys) {
            foreach ($allKeys as $key) {
                $allData[$key] = Cache::get($key);

                if (isset($request->keys)) {
                    Cache::put($key, $allData[$key], Carbon::now()->addMinutes($this->ttl));
                }
            }
            $allData = array_filter($allData);

            return response()->json($allData, 200);
        }
        return response()->json(["message" => "No data found!"], 200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $inputData = $request->all();

            $keyData = [];
            foreach ($inputData as $key => $value) {
                $keyData[] = $key;
                Cache::put($key, $value, Carbon::now()->addMinutes($this->ttl));
            }
            Cache::forever("allKeys", $keyData);

            return response()->json(['status' => 'success', 'statusCode' => 200, 'message' => 'Stored Successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'statusCode' => 500, 'message' => 'Server Error!'], 500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        try {
            $inputData = $request->all();

            foreach ($inputData as $key => $value) {
                if (Cache::has($key)) {
                    Cache::put($key, $value, Carbon::now()->addMinutes($this->ttl));
                }
            }

            return response()->json(['status' => 'success', 'statusCode' => 200, 'message' => 'Updated Successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'statusCode' => 500, 'message' => 'Server Error!'], 500);
        }
    }
}
