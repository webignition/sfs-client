<?php

namespace webignition\SfsClient;

class RequestFactory
{
    const KEY_EMAILS = 'emails';
    const KEY_EMAIL_HASHES = 'emailhashes';
    const KEY_IPS = 'ips';
    const KEY_USERNAMES = 'usernames';
    const KEY_OPTIONS = 'options';

    public static function create(array $data): Request
    {
        $request = new Request();

        foreach ($data as $key => $dataSet) {
            if (self::KEY_EMAILS === $key) {
                $request->addEmails($dataSet);
            }

            if (self::KEY_EMAIL_HASHES === $key) {
                $request->addEmailHashes($dataSet);
            }

            if (self::KEY_IPS === $key) {
                $request->addIps($dataSet);
            }

            if (self::KEY_USERNAMES === $key) {
                $request->addUsernames($dataSet);
            }

            if (self::KEY_OPTIONS === $key) {
                foreach ($dataSet as $option) {
                    $request->addOption($option);
                }
            }
        }

        return $request;
    }
}
