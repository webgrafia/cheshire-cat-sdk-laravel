<?php

namespace CheshireCatSdk\Http\Controllers;

use CheshireCatSdk\Facades\CheshireCatFacade as CheshireCat;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CheshireCatController extends Controller
{
    /**
     * Check the status of the Cheshire Cat API.
     *
     * @return \Illuminate\Http\Response
     */
    public function meowStatus()
    {
        try {
            $statusResponse = CheshireCat::getStatus();

            if ($statusResponse->getStatusCode() === 200) {
                return response("Cheshire Cat API connection successful!<br>Status Response: " . $statusResponse->getBody()->getContents(), 200);
            } else {
                return response("Cheshire Cat API connection failed!<br>Status Response: " . $statusResponse->getBody()->getContents(), $statusResponse->getStatusCode());
            }
        } catch (\Exception $e) {
            return response("Cheshire Cat API connection failed!<br>Error: " . $e->getMessage(), 500);
        }
    }


    public function meowHello()
    {
        try {
            $response = CheshireCat::message('Hello, who are you?');
            $data = json_decode($response->getBody(), true);
            return response($data, 200);
        } catch (\Exception $e) {
            return response("Cheshire Cat API connection failed!<br>Error: " . $e->getMessage(), 500);
        }
    }
}