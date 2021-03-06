<?php

namespace App\Entity;

use App\Repository\FigureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=FigureRepository::class)
 */
class Figure
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="figure", cascade={"remove"}, orphanRemoval=true)
     */
    private $comments;

    /**
     * @ORM\Column(type="string", length=25)
     * @Assert\Length(
     *      min = 2,
     *      max = 20,
     *      minMessage = "Le nom de la figure doit avoir au moins {{ limit }} caractères",
     *      maxMessage = "Le nom de la figure ne doit pas dépasser {{ limit }} caractères"
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255))
     * @Assert\Length(
     *      min = 10,
     *      max = 255,
     *      minMessage = "La description doit avoir au moins {{ limit }} caractères",
     *      maxMessage = "La description ne doit pas dépasser {{ limit }} caractères"
     * )
     */
    private $description;
    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity=Group::class, inversedBy="figures")
     */
    private $groupType;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="no")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity=Illustration::class, mappedBy="figure", orphanRemoval=true, cascade={"persist"})
     */
    private $illustrations;

    /**
     * @ORM\OneToMany(targetEntity=Video::class, mappedBy="figure", orphanRemoval=true, cascade={"persist"})
     */
    private $videos;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updateDate;

    public function __construct()
    {
        $this->illustrations = new ArrayCollection();
        $this->videos = new ArrayCollection();
        $this->date = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getGroupType(): ?Group
    {
        return $this->groupType;
    }

    public function setGroupType(?Group $groupType): self
    {
        $this->groupType = $groupType;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection|Illustration[]
     */
    public function getIllustrations(): Collection
    {
        return $this->illustrations;
    }

    public function addIllustration(Illustration $illustration): self
    {
        if (!$this->illustrations->contains($illustration)) {
            $this->illustrations[] = $illustration;
            $illustration->setFigure($this);
        }

        return $this;
    }

    public function removeIllustration(Illustration $illustration): self
    {
        if ($this->illustrations->contains($illustration)) {
            $this->illustrations->removeElement($illustration);
            // set the owning side to null (unless already changed)
            if ($illustration->getFigure() === $this) {
                $illustration->setFigure(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Video[]
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function addVideo(Video $video): self
    {
        if (!$this->videos->contains($video)) {
            $this->videos[] = $video;
            $video->setFigure($this);
        }

        return $this;
    }

    public function removeVideo(Video $video): self
    {
        if ($this->videos->contains($video)) {
            $this->videos->removeElement($video);
            // set the owning side to null (unless already changed)
            if ($video->getFigure() === $this) {
                $video->setFigure(null);
            }
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug($slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function computeSlug(SluggerInterface $slugger)
    {
        $this->slug = (string) $slugger->slug((string) $this)->lower();
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getUpdateDate(): ?\DateTimeInterface
    {
        return $this->updateDate;
    }

    public function setUpdateDate(?\DateTimeInterface $updateDate): self
    {
        $this->updateDate = $updateDate;

        return $this;
    }
}
