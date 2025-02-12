<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/comments/{postId}/comments",
     *     summary="Get all comments for a post",
     *     tags={"Comments"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="postId",
     *         in="path",
     *         required=true,
     *         description="ID of the post",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="List of comments"),
     *     @OA\Response(response=404, description="Post not found")
     * )
     */
    public function index($postId)
    {
        $post = Post::findOrFail($postId);
        return response()->json($post->comments()->with('user')->get());
    }

    /**
     * @OA\Post(
     *     path="/api/comments/{postId}/comments",
     *     summary="Create a new comment on a post",
     *     tags={"Comments"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="postId",
     *         in="path",
     *         required=true,
     *         description="ID of the post",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content"},
     *             @OA\Property(property="content", type="string", example="This is a comment.")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Comment created"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Post not found")
     * )
     */
    public function store(Request $request, $postId)
    {
        $request->validate([
            'content' => 'required|string|max:500',
        ]);

        $post = Post::findOrFail($postId);

        $comment = $post->comments()->create([
            'content' => $request->content,
            'user_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'Comment added successfully', 'comment' => $comment], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/comments/{id}",
     *     summary="Update a comment",
     *     tags={"Comments"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Comment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content"},
     *             @OA\Property(property="content", type="string", example="Updated comment text.")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Comment updated successfully"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Comment not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string|max:500',
        ]);

        $comment = Comment::findOrFail($id);

        if ($comment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->update(['content' => $request->content]);

        return response()->json(['message' => 'Comment updated successfully', 'comment' => $comment]);
    }

    /**
     * @OA\Delete(
     *     path="/api/comments/{id}",
     *     summary="Delete a comment",
     *     tags={"Comments"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Comment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Comment deleted successfully"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Comment not found")
     * )
     */
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);

        if ($comment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully']);
    }
}
