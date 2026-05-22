<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class NewLoginController extends Controller
{
    public function __invoke(Request $request)
    {

               /* $user = User::where('cedula', 30157966)->first();

                        if (!$user) {
                            return response()->json(['error' => 'User not found'], 404);
                        }

                        $token = $user->createToken($user)->plainTextToken;

                        return response()->json([
                            'token' => $token
                        ], 200);*/

        $tokenSVA = $request->header('svatoken');
        //return response()->json(['error' => $request->headers->all()], 400);
	//return response()->json(['error' => $request->header('svatoken')], 500);
        if (empty($tokenSVA)) {
            return response()->json(['error' => 'Token is required'], 400);
        }

        $client = new Client();
        $maxRetries = 3;
        $attempts = 0;
        $retryDelay = 1;


        while ($attempts < $maxRetries) {
            try {
                $response = $client->post('http://localhost:8080/auth', [
                    'json' => ['token' => $tokenSVA],
                    'headers' => [
                        'Accept'        => 'application/json',
                        'x-api-key' => 'Bearer 89c429ad-bbe9-4877-9c96-94fa2520ef1e',
                    ],
                ]);

                if ($response->getStatusCode() === 200) {
                    $data = json_decode($response->getBody(), true);

                    if (is_array($data) && isset($data['data'])) {
                        $user_data = $data['data'];
                        $user = User::where('cedula', $user_data['cedula'])->first();

                        if (!$user) {
                            return response()->json(['error' => 'User not found'], 404);
                        }

                        $token = $user->createToken($tokenSVA)->plainTextToken;

                        return response()->json([
                            'token' => $token
                        ], 200);
                    } else {
                        return response()->json($data, 500);
                        return response()->json(['error' => 'Invalid JSON structure from server'], 500);
                    }
                } else {
                    return response()->json(['error' => 'Failed to authenticate'], $response->getStatusCode());
                }
            } catch (RequestException $e) {
                if ($e->getCode() === 504) {
                    $attempts++;
                    if ($attempts >= $maxRetries) {
                        return response()->json([
                            'error' => 'Gateway Timeout: The authentication server did not respond in time. Please try again later.'
                        ], 504);
                    }
                    sleep($retryDelay);
                    $retryDelay *=5; // Incrementa el tiempo de espera para el próximo intento
                } else {
                    Log::error('Request error: ' . $e->getMessage());
                    return response()->json(['error' => 'Request error: ' . $e->getMessage()], 500);
                }
            } catch (\Exception $e) {
                Log::error('Unexpected error: ' . $e->getMessage());
                return response()->json(['error' => 'Unexpected error: ' . $e->getMessage()], 500);
            }
        }
}
}
