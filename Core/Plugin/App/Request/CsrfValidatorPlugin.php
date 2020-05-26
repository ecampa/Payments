<?php

namespace Payments\Core\Plugin\App\Request;

class CsrfValidatorPlugin
{


    /**
     *
     * @param $subject
     * @param $proceed
     * @param $request
     * @param $action
     * @return bool
     */
    public function aroundValidate($subject, $proceed, $request, $action)
    {
        if ($action instanceof \Payments\Core\Action\CsrfIgnoringAction) {
            return true;
        }

        return $proceed($request, $action);
    }
}
