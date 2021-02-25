<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler {

	/**
	 * A list of the exception types that should not be reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
		\Illuminate\Auth\AuthenticationException::class,
		\Illuminate\Auth\Access\AuthorizationException::class,
		\Symfony\Component\HttpKernel\Exception\HttpException::class,
		\Illuminate\Database\Eloquent\ModelNotFoundException::class,
		\Illuminate\Session\TokenMismatchException::class,
		\Illuminate\Validation\ValidationException::class,
	];

	/**
	 * Report or log an exception.
	 *
	 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
	 *
	 * @param \Exception $exception
	 * @return void
	 */
	public function report(Exception $exception){
		parent::report($exception);
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Exception $exception
	 * @return \Illuminate\Http\Response
	 */
	public function render($request, Exception $exception){
		if($exception instanceof ModelNotFoundException || $exception instanceof MethodNotAllowedHttpException){
			if($request->ajax() || $request->wantsJson()) return responseJson([ "error" => trans('messages.ResourceNotFound'), "status" => false, ], 404);
			else{
				if($request->is('admin') || $request->is('admin/*')){ return response()->view('admin.errors.404', [ 'title' => trans('messages.NotFound'), ], 404); }
				else if($request->is('api') || $request->is('api/*')){ return responseJson([ "error" => trans('messages.ResourceNotFound'), "UrlFound" => false, ], 404); }
				else{ return response()->view('errors.404', [], 404); }
			}
		}

		if($this->isHttpException($exception)){
			switch($exception->getStatusCode()){
				case 404: // not found
					if($request->ajax() || $request->wantsJson()) return responseJson([ "error" => trans('messages.ResourceNotFound'), "status" => false, ], 404);
					else{
						if($request->is('admin') || $request->is('admin/*')){ return response()->view('admin.errors.404', [ 'title' => trans('messages.NotFound'), ], 404); }
						else if($request->is('api') || $request->is('api/*')){ return responseJson([ "error" => trans('messages.ResourceNotFound'), "UrlFound" => false, ], 404); }
						else if($request->is('admin_files') || $request->is('admin_files/*') || $request->is('js') || $request->is('js/*') || $request->is('css') || $request->is('css/*') || $request->is('uploads') || $request->is('uploads/*')){ return responseJson([ "error" => trans('messages.ResourceNotFound'), "UrlFound" => false, ], 404); }
						else{ return response()->view('errors.404', [], 404); }
					}
					break;
				case 500: // internal server error
					if($request->ajax() || $request->wantsJson()) return responseJson([ 'error' => $exception->getMessage(), ], 401);
					else return response()->view('errors.500', [], 500);
					break;
				default:
					return $this->renderHttpException($exception);
					break;
			}
		}
		return parent::render($request, $exception);
	}

	/**
	 * Convert an authentication exception into an unauthenticated response.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Illuminate\Auth\AuthenticationException $exception
	 * @return \Illuminate\Http\Response
	 */
	protected function unauthenticated($request, AuthenticationException $exception){
		if($request->expectsJson()){
			return responseJson(['error' => 'Unauthenticated.'], 401);
		}
		return redirect()->guest(route('login'));
	}
}