<?php

namespace webignition\SfsClient;

class Request
{
    const KEY_EMAIL = 'email';
    const KEY_EMAIL_HASH = 'emailhash';
    const KEY_IP = 'ip';
    const KEY_USERNAME = 'username';

    const OPTION_NO_BAD_EMAIL = 'nobademail';
    const OPTION_NO_BAD_USERNAME = 'nobadusername';
    const OPTION_NO_BAD_IP = 'nobadip';
    const OPTION_NO_BAD_ALL = 'nobadall';
    const OPTION_NO_TOR_EXIT = 'notorexit';
    const OPTION_BAD_TOR_EXIT = 'badtorexit';

    const ENCODING = 'UTF-8';

    private $emails = [];
    private $emailHashes = [];
    private $ips = [];
    private $usernames = [];
    private $options = [];

    public function addEmails(array $emails)
    {
        $this->emails = $this->mergeAdditionalValues($this->emails, $emails);
    }

    public function addEmailHashes(array $emailHashes)
    {
        $this->emailHashes = $this->mergeAdditionalValues($this->emailHashes, $emailHashes);
    }

    public function addIps(array $ips)
    {
        $this->ips = $this->mergeAdditionalValues($this->ips, $ips);
    }

    public function addUsernames(array $usernames)
    {
        $this->usernames = $this->mergeAdditionalValues($this->usernames, $usernames);
    }

    public function addOption(string $option)
    {
        $this->options = $this->mergeAdditionalValues($this->options, [$option]);
    }

    public function getPayload(): array
    {
        $payload = [];

        if (!empty($this->emails)) {
            $payload[self::KEY_EMAIL] = $this->emails;
        }

        if (!empty($this->emailHashes)) {
            $payload[self::KEY_EMAIL_HASH] = $this->emailHashes;
        }

        if (!empty($this->ips)) {
            $payload[self::KEY_IP] = $this->ips;
        }

        if (!empty($this->usernames)) {
            $payload[self::KEY_USERNAME] = $this->usernames;
        }

        foreach ($this->options as $option) {
            $payload[$option] = true;
        }

        return $payload;
    }

    private function mergeAdditionalValues(array $data, array $additional)
    {
        return array_values(
            array_unique(
                array_merge($data, $this->normalizeStringData($additional))
            )
        );
    }

    private function normalizeStringData(array $data): array
    {
        return array_map(function ($value) {
            return mb_strtolower($value, self::ENCODING);
        }, $data);
    }
}
