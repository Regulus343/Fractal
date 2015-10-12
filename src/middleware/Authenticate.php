<?php namespace Regulus\Fractal\Middleware;

use Closure;
use Fractal;
use Regulus\Identify\Facade as Auth;
use Illuminate\Contracts\Auth\Guard;

class Authenticate {

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
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if ($this->auth->guest())
		{
			if ($request->ajax())
			{
				return response('Unauthorized.', 401);
			}
			else
			{
				return redirect()->guest(Fractal::url('login'))
					->with('messages', ['error' => Fractal::trans('messages.errors.log_in_required')]);
			}
		}
		else
		{
			$cmsRoles = Fractal::getSetting('CMS Roles', 'admin');
			if (Auth::isNot($cmsRoles))
			{
				if (config('cms.user_role_no_cms_access_log_out'))
					$this->auth->logout();

				return redirect()->to(config('cms.user_role_no_cms_access_uri'));
			}
		}

		return $next($request);
	}

}