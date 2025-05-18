<?php

namespace App\Http\Controllers;

use anlutro\LaravelSettings\Facade as Setting;
use Illuminate\Http\Request;

/**
 * This controller controls the global, app-specific and toggleble settings, such as if trainings are enabled.
 */
class GlobalSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  anlutro\LaravelSettings\Facade  $setting
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Setting $setting)
    {
        $this->authorize('index', $setting);

        return view('admin.globalsettings');
    }

    /**
     * Edit the requested resource
     *
     * @param  anlutro\LaravelSettings\Facade  $setting
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Request $request, Setting $setting)
    {
        $this->authorize('edit', $setting);

        $data = $request->validate([
            'trainingSOP' => 'required|url',
            'trainingSubDivisions' => 'required',
            'linkDomain' => 'required',
            'linkHome' => 'required|url',
            'linkJoin' => 'required|url',
            'linkContact' => 'required|url',
            'linkDiscord' => 'required|url',
            'linkMoodle' => 'url',
            'linkWiki' => 'url',
            'ptdCallsign' => 'required|max:3',
            'ptmEmail' => 'required|email',
            'ptmCID' => 'required|exists:App\Models\User,id',
        ]);

        // The setting dependency doesn't support null values, so we need to set it to false if it's not set
        isset($data['linkMoodle']) ? $linkMoodle = $data['linkMoodle'] : $linkMoodle = false;
        isset($data['linkWiki']) ? $linkWiki = $data['linkWiki'] : $linkWiki = false;

        Setting::set('trainingSOP', $data['trainingSOP']);
        Setting::set('trainingSubDivisions', $data['trainingSubDivisions']);
        Setting::set('linkDomain', $data['linkDomain']);
        Setting::set('linkHome', $data['linkHome']);
        Setting::set('linkJoin', $data['linkJoin']);
        Setting::set('linkContact', $data['linkContact']);
        Setting::set('linkDiscord', $data['linkDiscord']);
        Setting::set('linkMoodle', $linkMoodle);
        Setting::set('linkWiki', $linkWiki);
        Setting::set('ptdCallsign', $data['ptdCallsign']);
        Setting::set('ptmEmail', $data['ptmEmail']);
        Setting::set('ptmCID', $data['ptmCID']);
        Setting::save();

        ActivityLogController::danger('OTHER', 'Global Settings Updated');

        return redirect()->intended(route('admin.settings'))->withSuccess('Server settings successfully changed');
    }
}
