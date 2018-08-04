<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of MessengerUserProfile
 * <pre>
 * //Example json response
 * {
 *  "first_name": "Peter",
 *  "last_name": "Chang",
 *  "profile_pic": "https://fbcdn-profile-a.akamaihd.net/hprofile-ak-xpf1/v/t1.0-1/p200x200/13055603_10105219398495383_8237637584159975445_n.jpg?oh=1d241d4b6d4dac50eaf9bb73288ea192&oe=57AF5C03&__gda__=1470213755_ab17c8c8e3a0a447fed3f272fa2179ce",
 *  "locale": "en_US",
 *  "timezone": -7,
 * "gender": "male"
 * } 
 * </pre>
 * @author kwlok
 */
class MessengerUserProfile
{
    public $firstName;
    public $lastName;
    public $profilePic;
    public $locale;
    public $timezone;
    public $gender;
    /**
     * Constructor
     * @param array $data
     */
    public function __construct($data=[]) 
    {
        if (!empty($data))
            foreach ($data as $key => $value) {
                $this->{Helper::camelCase($key)} = $value;
            }
    }
    /**
     * Get data in array form
     * @return array
     */
    public function toArray()
    {
        return [
            'firstName'=>$this->firstName,
            'lastName'=>$this->lastName,
            'profilePic'=>$this->profilePic,
            'locale'=>$this->locale,
            'timezone'=>$this->timezone,
            'gender'=>$this->gender,
        ];
    }
}
