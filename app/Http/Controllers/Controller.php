<?php

/* Prompt (ChatGPT GPT-5 mini)
 * "The parent class Controller.php in app/http/Controllers is an empty abstract class. What would be its content? Does it work like this already?"
 */

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
