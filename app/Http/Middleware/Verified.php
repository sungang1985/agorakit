<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class Verified
{
  /**
  * The Guard implementation.
  *
  * @var Guard
  */
  protected $auth;

  /**
  * Create a new filter instance.
  *
  * @param  Guard  $auth
  * @return void
  */
  public function __construct(Guard $auth)
  {
    $this->auth = $auth;
  }

  /**
  * Check if the current user is verified ((s)he verified the email adress)
  */
  public function handle($request, Closure $next)
  {
    // if current user is not a member of the passed in group
    // curently it means the user must have membership level higher than 10

    if ($this->auth->guest()) {
      return redirect()->back()->with('message', trans('messages.not_logged_in'));
    }

    // we expect a url in the form /groups/{group_id}
    if ($request->user()->verified == 1)
    {
      return $next($request);
    }
    else
    {
      return redirect()->back()->with('message', trans('messages.email_not_verified'));
    }

  }
}
