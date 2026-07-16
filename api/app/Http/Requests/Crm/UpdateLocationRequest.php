<?php

namespace App\Http\Requests\Crm;

/**
 * A location's address is edited as a whole, so PUT is a full replacement and
 * requires the same fields as creation. Reuses StoreLocationRequest's rules.
 */
class UpdateLocationRequest extends StoreLocationRequest
{
}
