<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Repository\CheeseListingRepository;
use Carbon\Carbon;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CheeseListingRepository::class)]
#[ApiResource(normalizationContext:["groups" => "cheese_listing:read", "swagger_definition_name" => "Read"], 
            denormalizationContext:["groups" => "cheese_listing:write", "swagger_definition_name" => "Write"],
            operations: [
                new Get(normalizationContext: ['groups' => ['cheese_listing:read', 'cheese_listing:item:get']]),
            ],
            paginationItemsPerPage: 5)]
#[ApiFilter(BooleanFilter::class, properties:["isPublished"])]
#[ApiFilter(SearchFilter::class, properties:["title" => "partial", "description" => "partial"])]
#[ApiFilter(RangeFilter::class, properties:["price"])]
#[ApiFilter(PropertyFilter::class)]
class CheeseListing
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(length: 255)]
    #[Groups(["cheese_listing:read", "cheese_listing:write", "user:read", "user:write"])]
    #[Assert\NotBlank()]
    #[Assert\Length(min:2, max:50, maxMessage:"Describe your cheese in 50 chars or less")]
    private ?string $title = null;
    
    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["cheese_listing:read"])]
    #[Assert\NotBlank()]
    private ?string $description = null;
    
    #[ORM\Column]
    #[Groups(["cheese_listing:read", "cheese_listing:write", "user:read", "user:write"])]
    #[Assert\NotBlank()]
    private ?int $price = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column]
    private ?bool $isPublished = false;

    #[ORM\ManyToOne(inversedBy: 'cheeseListings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["cheese_listing:read", "cheese_listing:write"])]
    #[Assert\Valid()]
    private ?User $owner = null;

    public function __construct(string $title = null)
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->title = $title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    
    #[Groups(["cheese_listing:read"])]
    public function getShortDescription(): ?string
    {
        if (strlen($this->description) < 40) {
            return $this->description;
        }

        return substr($this->description, 0, 40).'...';
    }
    
    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    #[Groups(["cheese_listing:write", "user:write"])]
    #[SerializedName("description")]
    public function setTextDescription(string $description): static
    {
        $this->description = nl2br($description);

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    #[Groups(["cheese_listing:read"])]
    public function getCreatedAtAgo(): string
    {
        return Carbon::instance($this->getCreatedAt())->diffForHumans();
    }

    public function isPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }
}
