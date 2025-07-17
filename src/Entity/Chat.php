<?php

/** @noinspection DuplicatedCode */
/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
/** @noinspection PhpFullyQualifiedNameUsageInspection */

namespace KatalysisAiChatBot\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="`KatalysisChats`")
 */
class Chat
{
    /**
     * @var integer
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="`id`", type="integer", nullable=true)
     */
    protected $id;


    /**
     * @var datetime
     * @ORM\Column(name="`started`", type="datetime", nullable=true)
     */
    protected $started;


    /**
     * @var string
     * @ORM\Column(name="`location`", type="string", nullable=true)
     */
    protected $location = '';

    /**
     * @var string
     * @ORM\Column(name="`llm`", type="string", nullable=true)
     */
    protected $llm = '';

    /**
     * @var integer
     * @ORM\Column(name="`createdBy`", type="integer", nullable=true)
     */
    protected $createdBy;

    /**
     * @var datetime
     * @ORM\Column(name="`createdDate`", type="datetime", nullable=true)
     */
    protected $createdDate;
    
    
    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return datetime
     */
    public function getStarted()
    {
        return $this->started;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @return string
     */
    public function getLlm()
    {
        return $this->llm;
    }

    /**
     * @return integer
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @return datetime
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    
    /**
     * @param integer $id
     * @return Chat
     */
    public function setId($id)
    {
        $this->id = $id;
         return $this;
    }

    /**
     * @param datetime $started
     * @return Chat
     */
    public function setStarted($started)
    {
        $this->started = $started;
         return $this;
    }

    /**
     * @param string $location
     * @return Chat
     */
    public function setLocation($location)
    {
        $this->location = $location;
         return $this;
    }

    /**
     * @param string $llm
     * @return Chat
     */
    public function setLlm($llm)
    {
        $this->llm = $llm;
         return $this;
    }

    /**
     * @param integer $createdBy
     * @return Chat
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
         return $this;
    }

    /**
     * @param datetime $createdDate
     * @return Chat
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;
         return $this;
    }

    
}
