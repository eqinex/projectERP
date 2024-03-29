<?php


namespace DevelopmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Company
 *
 * @ORM\Table(name="company_code")
 * @ORM\Entity(repositoryClass="DevelopmentBundle\Repository\CompanyCodeRepository")
 */
class CompanyCode
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var  string
     *
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return CompanyCode
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

}