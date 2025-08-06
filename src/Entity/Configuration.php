<?php

namespace App\Entity;

use App\Repository\ConfigurationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ConfigurationRepository::class)]
#[ORM\Table(name: '`configuration`')]
#[ORM\UniqueConstraint(name: 'unique_config_key', columns: ['config_key'])]
class Configuration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $configKey = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $configValue = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['string', 'integer', 'boolean', 'json', 'text'])]
    private ?string $dataType = 'string';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $category = null;

    #[ORM\Column]
    private ?bool $isEditable = true;

    #[ORM\Column]
    private ?bool $isPublic = true;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConfigKey(): ?string
    {
        return $this->configKey;
    }

    public function setConfigKey(string $configKey): static
    {
        $this->configKey = $configKey;
        return $this;
    }

    public function getConfigValue(): ?string
    {
        return $this->configValue;
    }

    public function setConfigValue(?string $configValue): static
    {
        $this->configValue = $configValue;
        return $this;
    }

    public function getDataType(): ?string
    {
        return $this->dataType;
    }

    public function setDataType(string $dataType): static
    {
        $this->dataType = $dataType;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function isEditable(): ?bool
    {
        return $this->isEditable;
    }

    public function setIsEditable(bool $isEditable): static
    {
        $this->isEditable = $isEditable;
        return $this;
    }

    public function isPublic(): ?bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): static
    {
        $this->isPublic = $isPublic;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Get the typed value based on data type
     */
    public function getTypedValue(): mixed
    {
        if ($this->configValue === null) {
            return null;
        }

        return match ($this->dataType) {
            'integer' => (int) $this->configValue,
            'boolean' => filter_var($this->configValue, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($this->configValue, true),
            default => $this->configValue,
        };
    }

    /**
     * Set the typed value and convert to string for storage
     */
    public function setTypedValue(mixed $value): static
    {
        if ($value === null) {
            $this->configValue = null;
            return $this;
        }

        $this->configValue = match ($this->dataType) {
            'json' => json_encode($value),
            default => (string) $value,
        };

        return $this;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
} 