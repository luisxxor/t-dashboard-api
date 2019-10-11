<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *      schema="User",
 *      @OA\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="name",
 *          description="name",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="lastname",
 *          description="lastname",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="email",
 *          description="email",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="phone_number1",
 *          description="phone_number1",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="phone_number2",
 *          description="phone_number2",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="country_code",
 *          description="country_code",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="address_line1",
 *          description="address_line1",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="address_line2",
 *          description="address_line2",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="city",
 *          description="city",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="region",
 *          description="region",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="zipcode",
 *          description="zipcode",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="company_name",
 *          description="company_name",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="company_number",
 *          description="company_number",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="email_verified_at",
 *          description="email_verified_at",
 *          type="string",
 *          format="date-time"
 *      )
 * )
 */
class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray( $request )
    {
        return [
            'id'                    => $this->id,
            'name'                  => $this->name,
            'lastname'              => $this->lastname,
            'email'                 => $this->email,
            'phone_number1'         => $this->phone_number1,
            'phone_number2'         => $this->phone_number2,
            'country_code'          => $this->country_code,
            'address_line1'         => $this->address_line1,
            'address_line2'         => $this->address_line2,
            'city'                  => $this->city,
            'region'                => $this->region,
            'zipcode'               => $this->zipcode,
            'company_name'          => $this->company_name,
            'company_number'        => $this->company_number,
            'email_verified_at'     => $this->email_verified_at,
        ];
    }
}
