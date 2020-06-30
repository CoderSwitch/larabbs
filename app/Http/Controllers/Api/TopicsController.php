<?php

namespace App\Http\Controllers\Api;

use App\Models\Topic;
use Illuminate\Http\Request;
use App\Http\Resources\TopicResource;
use App\Http\Requests\Api\TopicRequest;

class TopicsController extends Controller
{
    public function store(TopicRequest $request, Topic $topic)
    {
        $topic->fill($request->all());
        $topic->user_id = $request->user()->id;
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
}