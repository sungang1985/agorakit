<?php

namespace App\Http\Controllers;

use App\Group;
use Auth;
use Illuminate\Http\Request;
use Flash;

class GroupController extends Controller
{
    public function __construct()
    {
        $this->middleware('verified', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
        $this->middleware('member', ['only' => ['edit', 'update', 'destroy']]);
        $this->middleware('cache', ['only' => ['index', 'show']]);
    }

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index(Request $request)
  {
      if (Auth::check()) {
          $groups = Group::with('membership')->orderBy('updated_at', 'desc')->paginate(12);
      } else {
          $groups = Group::orderBy('updated_at', 'desc')->paginate(12);
      }

      return view('groups.index')
    ->with('groups', $groups);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return Response
   */
  public function create()
  {
      return view('groups.create');
  }

  /**
   * Store a newly created resource in storage.
   *
   * @return Response
   */
  public function store(Request $request)
  {
      $group = new group();

      $group->name = $request->input('name');
      $group->body = $request->input('body');

      if ($group->isInvalid()) {
          // Oops.
      return redirect()->action('GroupController@create')
      ->withErrors($group->getErrors())
      ->withInput();
      }

      $group->save();

    // make the current user a member of the group
    $membership = \App\Membership::firstOrNew(['user_id' => $request->user()->id, 'group_id' => $group->id]);
      $membership->membership = 20;
      $membership->save();

      return redirect()->action('MembershipController@settings', [$group->id]);
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   *
   * @return Response
   */
  public function show($id)
  {
      $group = Group::findOrFail($id);

      if (Auth::check()) {
          $discussions = $group->discussions()->with('user', 'userReadDiscussion')->orderBy('updated_at', 'desc')->limit(5)->get();
      } else {
          $discussions = $group->discussions()->with('user')->orderBy('updated_at', 'desc')->limit(5)->get();
      }
      $files = $group->files()->with('user')->orderBy('updated_at', 'desc')->limit(5)->get();

      return view('groups.show')
    ->with('group', $group)
    ->with('discussions', $discussions)
    ->with('files', $files)
    ->with('tab', 'home');
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   *
   * @return Response
   */
  public function edit(Request $request, $group_id)
  {
      $group = Group::findOrFail($group_id);

      return view('groups.edit')
    ->with('group', $group)
    ->with('group', $group)
    ->with('tab', 'home');
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  int  $id
   *
   * @return Response
   */
  public function update(Request $request, $group_id)
  {
      $group = Group::findOrFail($group_id);
      $group->name = $request->input('name');
      $group->body = $request->input('body');

      $group->user()->associate(Auth::user());

      if ($group->isInvalid()) {
          // Oops.
      return redirect()->action('GroupController@edit', $group->id)
      ->withErrors($group->getErrors())
      ->withInput();
      }

      $group->save();

      $request->session()->flash('message', trans('messages.ressource_updated_successfully'));

      return redirect()->action('GroupController@show', [$group->id]);
  }

  /**
   * Show the revision history of the group
   */
  public function history($group_id)
  {
    $group = Group::findOrFail($group_id);

    return view('groups.history')
    ->with('group', $group)
    ->with('tab', 'home');
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   *
   * @return Response
   */
  public function destroy($id)
  {
  }
}
