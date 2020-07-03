<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Models\Image;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\TopicResource;
use App\Http\Requests\Api\TopicRequest;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Tymon\JWTAuth\Facades\JWTAuth;

class TopicsController extends Controller
{
    public function index(Request $request, Topic $topic)
    {
        $topics = QueryBuilder::for(Topic::class)
            ->allowedIncludes('user', 'category')
            ->allowedFilters([
                'title',
                AllowedFilter::exact('category_id'),
                AllowedFilter::scope('withOrder')->default('recentReplied'),
            ])
            ->paginate(10);

        return TopicResource::collection($topics);
    }

    public function store(TopicRequest $request, Topic $topic)
    {
        $topic->fill($request->all());
        $topic->user_id = $request->user()->id;
        if ($request->topic_image_id) {
            $image = Image::find($request->topic_image_id);

            $topic['image'] = $image->path;
        }
        $topic->save();

        return new TopicResource($topic);
    }

    public function update(TopicRequest $request)
    {
        $topicId =  $request['id'];
        $topic = Topic::where('id', $topicId)->first();
        $this->authorize('update', $topic);

        $topic->update($request->all());
        return new TopicResource($topic);
    }

    /*
     * 删除舞种接口
     * ***/
    public function deletetopic(Request $request)
    {

        $topicId = $request['id'];

        $topic = Topic::where('id', $topicId)->first();
        if (!$topic){
            return response(null,99);
        }
        $this->authorize('destroy', $topic);
        $topic->delete();
        return response(null, 204);
    }

    public function userIndex(Request $request)
    {
        $token = JWTAuth::getToken();
        $user = JWTAuth::authenticate($token);

        if ($request->user_id){
            $user = User::where('id', $request->user_id)->first();
        }



        $query = $user->topics()->getQuery();

        $topics = QueryBuilder::for($query)
            ->allowedIncludes('user', 'category')
            ->allowedFilters([
                'title',
                AllowedFilter::exact('category_id'),
                AllowedFilter::scope('withOrder')->default('recentReplied'),
            ])
            ->paginate();

        return TopicResource::collection($topics);
    }

    public function show(Topic $topic)
    {
        return new TopicResource($topic);
    }
}