<?php declare(strict_types=1);

namespace PrivatePackagist\ApiClient\Payload;

class VcsPackageConfig
{
    /** @var string */
    private $url;
    /** @var ?int */
    private $credentialId;
    /** @var string */
    private $type;
    /** @var ?string */
    private $defaultSubrepositoryAccess;

    /**
     * @param string $url
     * @param ?int $credentialId
     * @param string $type
     * @param ?string $defaultSubrepositoryAccess
     */
    public function __construct($url, $credentialId, $type, $defaultSubrepositoryAccess)
    {
        $this->url = $url;
        $this->credentialId = $credentialId;
        $this->type = $type;
        $this->defaultSubrepositoryAccess = $defaultSubrepositoryAccess;
    }

    /**
     * @return array{repoType: string, repoUrl: string, credentials: ?int, defaultSubrepositoryAccess?: string}
     */
    public function toParameters(): array
    {
        $data = [
            'repoType' => $this->type,
            'repoUrl' => $this->url,
            'credentials' => $this->credentialId,
        ];

        if ($this->defaultSubrepositoryAccess) {
            $data['defaultSubrepositoryAccess'] = $this->defaultSubrepositoryAccess;
        }

        return $data;
    }
}
