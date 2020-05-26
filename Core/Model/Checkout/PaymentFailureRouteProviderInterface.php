<?php
namespace Payments\Core\Model\Checkout;;

interface PaymentFailureRouteProviderInterface
{

    /**
     * Returns the route path that the customer will be redirected on checkout payment failures
     *
     * @return string Failure page route path
     */
    public function getFailureRoutePath();

}
