<?php

if (! function_exists('present')) {
    /**
     * Return a view with the data transformed.
     *
     * @return \Illuminate\Http\Response
     */
    function present()
    {
        return Response::present(...func_get_args());
    }
}
