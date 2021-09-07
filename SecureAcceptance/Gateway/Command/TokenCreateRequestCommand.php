<?php
namespace Payments\SecureAcceptance\Gateway\Command;

use Payments\Core\Gateway\Command\CreateRequestCommand;

class TokenCreateRequestCommand extends \Payments\Core\Gateway\Command\CreateRequestCommand
{

    const COMMAND_CODE = 'create_token';

}
