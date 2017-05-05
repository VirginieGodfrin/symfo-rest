<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Preference;

/**
 * User
 *
 * @ORM\Table(name="user", 
 * uniqueConstraints={@ORM\UniqueConstraint(name="users_email_unique",columns={"email"})}
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 */
class User
{
    const MATCH_VALUE_THRESHOLD = 25;
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255)
     */
    private $lastname;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
    * @ORM\OneToMany(targetEntity="Preference", mappedBy="user")
    */
    private $preferences;


    

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     *
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     *
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->preferences = new ArrayCollection();
    }

    /**
     * Add preference
     *
     * @param \AppBundle\Entity\Preference $preference
     *
     * @return User
     */
    public function addPreference(Preference $preference)
    {
        $this->preferences[] = $preference;

        return $this;
    }

    /**
     * Remove preference
     *
     * @param \AppBundle\Entity\Preference $preference
     */
    public function removePreference(Preference $preference)
    {
        $this->preferences->removeElement($preference);
    }

    /**
     * Get preferences
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPreferences()
    {
        return $this->preferences;
    }

    public function preferencesMatch($themes){
        
        $matchVal = 0;

        foreach ($this->preferences as  $preference) {
            foreach ($themes as $theme) {
                //match() verifie si le nom de theme est le m^me que celui de preference
                if ($preference->match($theme)) {
                    $matchVal += $preference->getValue() * $theme->getValue();
                }
            }
        }
        return $matchVal >= self::MATCH_VALUE_THRESHOLD;
    }
}
