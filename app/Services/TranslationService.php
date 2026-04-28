<?php

namespace App\Services;

use App\Repositories\TranslationRepository;
use Illuminate\Support\Facades\Cache;

class TranslationService
{
    private $translations;

    public function __construct(TranslationRepository $translations)
    {
        $this->translations = $translations;
    }

    public function search(array $filters, $perPage)
    {
        return $this->translations->paginate($filters, $this->normalizePerPage($perPage));
    }

    public function find($id)
    {
        return $this->translations->find($id);
    }

    public function store(array $data)
    {
        $translation = $this->translations->create($data);
        $this->bustExportCache();

        return $translation;
    }

    public function update($id, array $data)
    {
        $translation = $this->translations->update($id, $data);
        $this->bustExportCache();

        return $translation;
    }

    public function delete($id)
    {
        $this->translations->delete($id);
        $this->bustExportCache();
    }

    public function export($locale, array $filters = [])
    {
        $version = (int) Cache::get('translations_export_version', 1);
        $cacheKey = 'translations.export.' . $version . '.' . md5($locale . '|' . json_encode($filters));

        return Cache::remember($cacheKey, 10, function () use ($locale, $filters) {
            return $this->translations->export($locale, $filters);
        });
    }

    private function normalizePerPage($perPage)
    {
        $perPage = (int) $perPage;

        if ($perPage < 1) {
            return 50;
        }

        return min($perPage, 100);
    }

    private function bustExportCache()
    {
        $version = (int) Cache::get('translations_export_version', 1);
        Cache::forever('translations_export_version', $version + 1);
    }
}
