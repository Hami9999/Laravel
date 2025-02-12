<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/posts/all-posts",
     *     summary="Get all posts",
     *     tags={"Posts"},
     *     @OA\Response(response=200, description="Successful response")
     * )
     */
    public function index()
    {
        $posts = Cache::remember('posts', now()->addMinutes(10), function () {
            return Post::with('user')->latest()->get();
        });

        return response()->json($posts);
    }

    /**
     * @OA\Post(
     *     path="/api/posts/add-post",
     *     summary="Create a new post",
     *     tags={"Posts"},
     *     security={{"jwt":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "content"},
     *             @OA\Property(property="title", type="string", example="My Blog Post"),
     *             @OA\Property(property="content", type="string", example="This is the content of the post")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Post created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="My Blog Post"),
     *             @OA\Property(property="content", type="string", example="This is the content of the post"),
     *             @OA\Property(property="user_id", type="integer", example=10),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2024-02-12T12:34:56Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2024-02-12T12:34:56Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The title field is required.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $post = Auth::user()->posts()->create($validated);

        Cache::forget('posts');

        return response()->json($post, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/posts/post/{id}",
     *     summary="Get a specific post",
     *     tags={"Posts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="My Blog Post"),
     *             @OA\Property(property="content", type="string", example="This is the content of the post"),
     *             @OA\Property(property="user_id", type="integer", example=10),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2024-02-12T12:34:56Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2024-02-12T12:50:30Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Post not found.")
     *         )
     *     )
     * )
     */
    public function show(Post $post)
    {
        $cachedPost = Cache::remember("post_{$post->id}", now()->addMinutes(10), function () use ($post) {
            return $post->load('user');
        });

        return response()->json($cachedPost);
    }

    /**
     * @OA\Put(
     *     path="/api/posts/post/{id}",
     *     summary="Update a post",
     *     tags={"Posts"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "content"},
     *             @OA\Property(property="title", type="string", example="Updated Title"),
     *             @OA\Property(property="content", type="string", example="Updated content of the post")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Updated Title"),
     *             @OA\Property(property="content", type="string", example="Updated content of the post"),
     *             @OA\Property(property="user_id", type="integer", example=10),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2024-02-12T12:34:56Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2024-02-12T12:50:30Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Post not found.")
     *         )
     *     )
     * )
     */
    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $post->update($validated);

        Cache::forget("post_{$post->id}");
        Cache::forget('posts');

        return response()->json($post);
    }

    /**
     * @OA\Delete(
     *     path="/api/posts/post/{id}",
     *     summary="Delete a post",
     *     description="Deletes a blog post by its ID",
     *     tags={"Posts"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Post soft deleted")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Post not found.")
     *         )
     *     )
     * )
     */
    public function destroy(Post $post)
    {
        $post->delete();
        Cache::forget("post_{$post->id}");
        Cache::forget('posts');

        return response()->json(['message' => 'Post deleted']);
    }

    /**
     * @OA\Patch(
     *     path="/api/posts/post/{id}/restore",
     *     summary="Restore a deleted post",
     *     description="Restores a soft-deleted blog post by its ID",
     *     tags={"Posts"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post restored successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Post restored successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Post not found")
     *         )
     *     )
     * )
     */
    public function restore($id)
    {
        $post = Post::withTrashed()->findOrFail($id);

        $post->restore();
        Cache::forget("post_{$post->id}");
        Cache::forget('posts');

        return response()->json(['message' => 'Post restored']);
    }

    /**
     * @OA\Get(
     *     path="/api/posts/post/search",
     *     summary="Search for blog posts",
     *     description="Search blog posts by title or content",
     *     tags={"Posts"},
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Search keyword"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Search query is required",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Search query is required")
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $query = $request->input('query');

        if (!$query) {
            return response()->json(['message' => 'Search query is required'], 400);
        }

        $posts = Cache::remember("search_{$query}", now()->addMinutes(10), function () use ($query) {
            return Post::where('title', 'LIKE', "%{$query}%")
                ->orWhere('content', 'LIKE', "%{$query}%")
                ->with('user')
                ->latest()
                ->get();
        });

        return response()->json($posts);
    }
}
