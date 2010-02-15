<?php
class AssetsController extends AppController {
	
	public $helpers = array('Cache');
	public $components = array('RequestHandler', 'Wildflower.JlmPackager');
	public $paginate = array(
        'limit' => 12,
        'order' => array('created' => 'desc')
    );
	
	function admin_create() {
	    $this->Asset->create($this->data);
	    
	    if (!$this->Asset->validates()) {
	        $this->feedFileManager();
	        return $this->render('admin_index');
	    }
	    
	    // @TODO replace upload logic with Asset::upload()
	    
	    // Check if file with the same name does not already exist
	    $fileName = trim($this->data[$this->modelClass]['file']['name']);
        $uploadPath = Configure::read('Wildflower.uploadDirectory') . DS . $fileName;
        
        // Rename file if already exists
        $i = 1;
        while (file_exists($uploadPath)) {
            // Append a number to the end of the file,
            // if it alredy has one increase it
            $newFileName = explode('.', $fileName);
            $lastChar = mb_strlen($newFileName[0], Configure::read('App.encoding')) - 1;
            if (is_numeric($newFileName[0][$lastChar]) and $newFileName[0][$lastChar - 1] == '-') {
                $i = intval($newFileName[0][$lastChar]) + 1;
                $newFileName[0][$lastChar] = $i;
            } else {
                $newFileName[0] = $newFileName[0] . "-$i";
            }
            $newFileName = implode('.', $newFileName);
            $uploadPath = Configure::read('Wildflower.uploadDirectory') . DS . $newFileName;
            $fileName = $newFileName;
        }
   
        // Upload file
        $isUploaded = @move_uploaded_file($this->data[$this->modelClass]['file']['tmp_name'], $uploadPath);
        
        if (!$isUploaded) {
            $this->Asset->invalidate('file', 'File can`t be moved to the uploads directory. Check permissions.');
            $this->feedFileManager();
            return $this->render('admin_index');
        }
        
        // Make this file writable and readable
        chmod($uploadPath, 0777);
        
        $this->Asset->data[$this->modelClass]['name'] = $fileName;
        if (empty($this->Asset->data[$this->modelClass]['title'])) {
            $this->Asset->data[$this->modelClass]['title'] = str_replace(array('.jpg', '.jpeg', '.gif', '.png'), array('', '', '', ''), $fileName);
        }
        $this->Asset->data[$this->modelClass]['mime'] = $this->Asset->data[$this->modelClass]['file']['type'];
        
        $this->Asset->save();
        
        $this->redirect($this->referer(Configure::read('Wildflower.prefix') . '/assets'));
	}

	/**
	 * Files overview
	 *
	 */
	function admin_index($filter=null) {
        if($filter){
			$this->paginate = array(
			'conditions' => array('Asset.category_id' => $filter),
			'limit' => 12,
			'order' => array('created' => 'desc')
			);
		}
		$this->feedFileManager($filter);
	}
	
	/**
	 * Delete an upload
	 *
	 * @param int $id
	 */
	 // @TODO make require a POST
	function admin_delete($id) {
	    $this->Asset->delete($id);
		$this->redirect(array('action' => 'index'));
	}
	
	/**
	 * Edit a file
	 *
	 * @param int $id
	 */
	function admin_edit($id) {
		$this->data = $this->Asset->findById($id);
		$this->pageTitle = $this->data[$this->modelClass]['title'];
	}
	
	/**
	 * Insert image dialog
	 *
	 * @param int $limit Number of images on one page
	 */
	function admin_insert_image() {
		$this->autoLayout = false;
		$this->paginate['limit'] = 10;
		$this->paginate['conditions'] = "{$this->modelClass}.mime LIKE 'image%'";
		$images = $this->paginate($this->modelClass);
		$this->set('images', $images);
	}
	
	function admin_browse_images() {
		$this->paginate['limit'] = 6;
		$this->paginate['conditions'] = "{$this->modelClass}.mime LIKE 'image%'";
		$images = $this->paginate($this->modelClass);
		$this->set('images', $images);
	}
	
	function admin_update() {
	    $this->Asset->create($this->data);
	    if (!$this->Asset->exists()) return $this->cakeError('object_not_found');
	    $this->Asset->saveField('title', $this->data[$this->modelClass]['title']);
	    $this->redirect(array('action' => 'edit', $this->Asset->id));
	}
	
	function beforeFilter() {
		parent::beforeFilter();
		
		// Upload limit information
        $postMaxSize = ini_get('post_max_size');
        $uploadMaxSize = ini_get('upload_max_filesize');
        $size = $postMaxSize;
        if ($uploadMaxSize < $postMaxSize) {
            $size = $uploadMaxSize;
        }
        $size = str_replace('M', 'MB', $size);
        $limits = "Maximum allowed file size: $size";
        $this->set('uploadLimits', $limits);
	}
    
    /**
     * Output parsed JLM javascript file
     *
     * The output is cached when not in debug mode.
     */
    function admin_jlm() {
        $javascripts = Cache::read('admin_jlm'); 
        if (empty($javascripts) or Configure::read('debug') > 0) {
            $javascripts = $this->JlmPackager->concate();
            Cache::write('admin_jlm', $javascripts);
			// this makes the debugkit work with wf
			Configure::write('debug', 0);
			die($javascripts);
        }
        
        $this->layout = false;
        $this->set(compact('javascripts'));
        $this->RequestHandler->respondAs('application/javascript');
        
        $cacheSettings = Cache::settings();
        $file = CACHE . $cacheSettings['prefix'] . 'admin_jlm';
        $this->JlmPackager->browserCacheHeaders(filemtime($file));
        
        Configure::write('debug', 0);
    }
    
    function thumbnail_by_id($id, $width = 120, $height = 120, $crop = 0) {
        $asset = $this->Asset->read(null, $id);
        $this->thumbnail($asset['Asset']['name'], $width, $height, $crop);
    }
    
    /**
     * Create a thumbnail from an image, cache it and output it
     *
     * @param $imageName File name from webroot/uploads/
     */
    function thumbnail($imageName, $width = 120, $height = 120, $crop = 0) {
        $this->autoRender = false;
        
        $imageName = str_replace(array('..', '/'), '', $imageName); // Don't allow escaping to upper directories

        $width = intval($width);
        if ($width > 2560) {
        	$width = 2560;
        }

        $height = intval($height);
        if ($height > 1600) {
        	$height = 1600;
        }

        $cachedFileName = join('_', array($imageName, $width, $height, $crop)) . '.jpg';
        $cacheDir = Configure::read('Wildflower.thumbnailsCache');
        $cachedFilePath = $cacheDir . DS . $cachedFileName;

        $refreshCache = false;
        $cacheFileExists = file_exists($cachedFilePath);
        if ($cacheFileExists) {
        	$cacheTimestamp = filemtime($cachedFilePath);
        	$cachetime = 60 * 60 * 24 * 14; // 14 days
        	$border = $cacheTimestamp + $cachetime;
        	$now = time();
        	if ($now > $border) {
        		$refreshCache = true;
        	}
        }

        if ($cacheFileExists && !$refreshCache) {
        	return $this->_renderJpeg($cachedFilePath);
        } else {
        	// Create cache and render it
        	$sourceFile = Configure::read('Wildflower.uploadDirectory') . DS . $imageName;
        	if (!file_exists($sourceFile)) {
        		return trigger_error("Thumbnail generator: Source file $sourceFile does not exists.");
        	}

        	App::import('Vendor', 'phpThumb', array('file' => 'phpthumb.class.php'));

        	$phpThumb = new phpThumb();

        	$phpThumb->setSourceFilename($sourceFile);
        	$phpThumb->setParameter('config_output_format', 'jpeg');

        	$phpThumb->setParameter('w', intval($width));
        	$phpThumb->setParameter('h', intval($height));
        	$phpThumb->setParameter('zc', intval($crop));

        	if ($phpThumb->GenerateThumbnail()) {
        		$phpThumb->RenderToFile($cachedFilePath);
        		return $this->_renderJpeg($cachedFilePath);
        	} else {
        		return trigger_error("Thumbnail generator: Can't GenerateThumbnail.");
        	}
        }
    }
    
    function _renderJpeg($cachedFilePath) {
        $this->JlmPackager->browserCacheHeaders(filemtime($cachedFilePath), 'image/jpeg');
        
        $fileSize = filesize($cachedFilePath);
        header("Content-Length: $fileSize");
        
        $cache = fopen($cachedFilePath, 'r');
        fpassthru($cache);
        fclose($cache);
    }
	
	private function feedFileManager($filter) {
		// Categories for select box
        $categories = $this->Asset->Category->find('list', array('fields' => array('id', 'title'), 'conditions' => array('Category.parent_id' => 61)));
	    $this->pageTitle = 'Files';
	    $files = $this->paginate($this->modelClass);
        $this->set(compact('files','filter','categories'));
	}
    
}
