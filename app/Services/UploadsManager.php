<?php


namespace App\Services;

use Carbon\Carbon;
use Dflydev\ApacheMimeTypes\PhpRepository;
use Illuminate\Support\Facades\Storage;

/**
 * 文件上传
 * Nginx 需要 client_max_body_size 1024m; 配置在 http 项下
 * PHP 需要配置 php-fpm.ini
 *   file_uploads on
 *   post_max_size 1024m
 *   max_execution_time = 900
 *   max_input_time = 900
 *   memory_limit = 1024M 这个可以不用修改
 *
 * Class UploadsManager
 * @package App\Services
 */
class UploadsManager
{
    protected $disk;
    protected $mimeDetect;

    public function __construct(PhpRepository $mimeDetect)
    {
        $this->disk = Storage::disk(config('blog.uploads.storage'));
        $this->mimeDetect = $mimeDetect;
    }

    /**
     * @param string $folder
     * @return array
     */
    public function folderInfo($folder)
    {
        $folder = $this->cleanFolder($folder);

        $breadcrumbs = $this->breadcrumbs($folder);
        $slice = array_slice($breadcrumbs, -1);
        $folderName = current($slice);
        $breadcrumbs = array_slice($breadcrumbs, 0, -1);

        $subfolders = [];
        foreach (array_unique($this->disk->directories($folder)) as $subfolder) {
            $subfolders["/$subfolder"] = basename($subfolder);
        }

        $files = [];
        foreach ($this->disk->files($folder) as $path) {
            $files[] = $this->fileDetails($path);
        }

        return compact(
            'folder',
            'folderName',
            'breadcrumbs',
            'subfolders',
            'files'
        );
    }

    protected function cleanFolder($folder)
    {
        return '/'.trim(str_replace('..', '', $folder), '/');
    }

    /**
     * 返回当前目录路径
     *
     * @param $folder
     * @return mixed
     */
    protected function breadcrumbs($folder)
    {
        $folder = trim($folder, '/');
        $crumbs = ['/' => 'root'];
        if (empty($folder)) {
            return $crumbs;
        }
        $folders = explode('/', $folder);
        $build = '';
        foreach ($folders as $folder) {
            $build .= '/'.$folder;
            $crumbs[$build] = $folder;
        }

        return $crumbs;
    }

    /**
     * 返回文件详细数组
     *
     * @param $path
     * @return array
     */
    protected function fileDetails($path)
    {
        $path = '/'. ltrim($path, '/');

        return [
            'name' => basename($path),
            'fullPath' => $path,
            'webPath' => $this->fileWebPath($path),
            'mimeType' => $this->fileMimeType($path),
            'size' => $this->fileSize($path),
            'modified' => $this->fileModified($path),
        ];
    }

    /**
     * 返回文件完整的web路径
     *
     * @param $path
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\UrlGenerator|string
     */
    public function fileWebPath($path)
    {
        $path = rtrim(config('blog.uploads.webpath'), '/'). '/'. ltrim($path, '/');
        return url($path);
    }

    /**
     * 返回文件Mime类型
     *
     * @param $path
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\UrlGenerator|string
     */
    public function fileMimeType($path)
    {
        return $this->mimeDetect->findType(pathinfo($path, PATHINFO_EXTENSION));
    }

    /**
     * 返回文件大小
     *
     * @param $path
     * @return int
     */
    public function fileSize($path)
    {
        return $this->disk->size($path);
    }

    /**
     * 返回文件最后修改时间
     *
     * @param $path
     * @return Carbon
     */
    public function fileModified($path)
    {
        return Carbon::createFromTimestamp($this->disk->lastModified($path));
    }

    /**
     * 创建新目录
     */
    public function createDirectory($folder)
    {
        $folder = $this->cleanFolder($folder);

        if ($this->disk->exists($folder)) {
            return "Folder '$folder' already exists.";
        }

        return $this->disk->makeDirectory($folder);
    }

    /**
     * 删除目录
     */
    public function deleteDirectory($folder)
    {
        $folder = $this->cleanFolder($folder);

        $filesFolders = array_merge(
            $this->disk->directories($folder),
            $this->disk->files($folder)
        );
        if (! empty($filesFolders)) {
            return "Directory must be empty to delete it.";
        }

        return $this->disk->deleteDirectory($folder);
    }

    /**
     * 删除文件
     */
    public function deleteFile($path)
    {
        $path = $this->cleanFolder($path);

        if (! $this->disk->exists($path)) {
            return "File does not exist.";
        }

        return $this->disk->delete($path);
    }

    /**
     * 保存文件
     */
    public function saveFile($path, $content)
    {
        $path = $this->cleanFolder($path);

        if ($this->disk->exists($path)) {
            return "File already exists.";
        }

        return $this->disk->put($path, $content);
    }
}
