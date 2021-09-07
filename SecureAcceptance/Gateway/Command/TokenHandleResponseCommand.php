<?php
namespace Payments\SecureAcceptance\Gateway\Command;

use Payments\Core\Gateway\Command\HandleResponseCommand;

class TokenHandleResponseCommand extends \Payments\Core\Gateway\Command\HandleResponseCommand
{

    const COMMAND_NAME = 'process_token';

}
