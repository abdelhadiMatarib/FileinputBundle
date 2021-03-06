<?php

namespace EMC\FileinputBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeExtensionGuesser;
use EMC\FileinputBundle\Driver\DriverInterface;

/**
 * Abstract File Entity
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class File implements FileInterface {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;
    
    /**
     * @ORM\Column(type="string", unique=true)
     * @Gedmo\UploadableFilePath
     * @var string
     */
    protected $path;

    /**
     * @ORM\Column(type="string")
     * @Gedmo\UploadableFileMimeType
     * @var string
     */
    protected $mimeType;

    /**
     * @ORM\Column(type="decimal")
     * @Gedmo\UploadableFileSize
     * @var integer
     */
    protected $size;

	/**
	 * @ORM\Column(type="decimal", nullable=true)
	 * @var integer
	 */
	protected $height;

	/**
	 * @ORM\Column(type="decimal", nullable=true)
	 * @var integer
	 */
	protected $width;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $driver;

    /**
     * @var \EMC\FileinputBundle\Driver\DriverInterface
     */
    private $_driver;
    
    public function __toString() {
        return $this->getUrl();
    }

    public function __clone() {
        $this->id = null;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Set name
     *
     * @param string $name
     * @return File
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     * Set path
     *
     * @param string $path
     *
     * @return File
     */
    public function setPath($path) {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * Set mimeType
     *
     * @param string $mimeType
     *
     * @return File
     */
    public function setMimeType($mimeType) {

        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * Get mimeType
     *
     * @return string
     */
    public function getMimeType() {
        return $this->mimeType;
    }

    /**
     * Set size
     *
     * @param string $size
     *
     * @return File
     */
    public function setSize($size) {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return string
     */
    public function getSize() {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getExtension() {
        $mimeTypeGuesser = new MimeTypeExtensionGuesser;
        return $mimeTypeGuesser->guess($this->mimeType);
    }

    public function getHumanReadableSize($dec = 2) {
        $sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = floor((strlen($this->size) - 1) / 3);
        return sprintf("%.{$dec}f %s", $this->size / pow(1024, $factor), @$sizes[$factor]);
    }

    public function isImage() {
        return substr($this->mimeType, 0, 6) === 'image/';
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getUrl() {
        return $this->_driver ? $this->_driver->getUrl($this->path) : substr($this->path, 1);
    }
    
    /**
     * Get path
     *
     * @return string
     */
    public function getThumbnail() {
        return $this->_driver ? $this->_driver->getThumbnail($this->path) : null;
    }
    
    /**
     * Get path
     *
     * @return string
     */
    public function render() {
        return $this->_driver ? $this->_driver->render($this->path) : null;
    }

    public function getMetadata() {
        return array(
            'id'  => $this->getId(),
            'name' => $this->getName(),
            'path' => $this->getUrl(),
            'mimeType' => $this->getMimeType(),
            'size' => $this->getHumanReadableSize(),
            'extension' => $this->getExtension()
        );
    }

    /**
     * @return string
     */
    function getDriver() {
        return $this->driver;
    }

    /**
     * @param string $driver
     * @return FileInterface
     */
    function setDriver($driver, DriverInterface $_driver = null) {
        $this->driver = $driver;
        $this->_driver = $_driver;
        return $this;
    }

	/**
	 * @return int
	 */
	public function getHeight()
	{
		return $this->height;
	}

	/**
	 * @return int
	 */
	public function getWidth()
	{
		return $this->width;
	}

	/**
	 * @ORM\PrePersist
	 */
	public function onPrePersist(){
		try {
			if ($this->path instanceof UploadedFile && substr($this->path->getMimeType(), 0, 6) === 'image/'){
				if ($info = getimagesize($this->path->getPathname())){
					list($this->width, $this->height) = $info;
				}
			}
		} catch (\Exception $exception){}
	}

	/**
	 * @param int $width
	 */
	public function setWidth($width)
	{
		$this->width = $width;
	}

	/**
	 * @param int $height
	 * @return File
	 */
	public function setHeight($height)
	{
		$this->height = $height;
		return $this;
	}


}
