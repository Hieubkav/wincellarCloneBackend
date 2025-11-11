// Frontend API Client Template with Cache Version Support
// File: lib/api/your-resource.ts

import { apiFetch } from "./client";

// Type definitions
export interface YourItem {
  id: number;
  name: string;
  // Add more fields...
}

export interface YourItemsResponse {
  data: YourItem[];
  meta: {
    cache_version: number;
    updated_at?: string;
  };
}

/**
 * Fetch items with cache invalidation support
 * 
 * Features:
 * - Time-based: Revalidate every 10s (fallback)
 * - On-demand: Instant update via webhook
 * - Cache version tracking
 */
export async function fetchYourItems(): Promise<YourItem[]> {
  const response = await apiFetch<YourItemsResponse>("v1/your-endpoint", {
    // Revalidate every 10 seconds (balance between freshness and performance)
    // Backend on-demand revalidation will update faster (1-2s)
    next: { revalidate: 10 },
  });
  
  // Optional: Log cache version in development
  if (process.env.NODE_ENV === 'development') {
    console.log(`Cache version: ${response.meta.cache_version}`);
  }
  
  return response.data;
}

/**
 * Fetch single item
 */
export async function fetchYourItem(id: number): Promise<YourItem> {
  const response = await apiFetch<{ data: YourItem; meta: { cache_version: number } }>(
    `v1/your-endpoint/${id}`,
    {
      next: { revalidate: 10 },
    }
  );
  
  return response.data;
}
