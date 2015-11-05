<?php

namespace LearnTube\Http\Controllers;

use LearnTube\Video;
use Illuminate\Http\Request;
use LearnTube\Http\Requests;
use Illuminate\Support\Facades\Auth;
use LearnTube\Http\Controllers\Controller;

class VideoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if ( Auth::check() ) {
            $videos = Video::personal()->get();
            return view('videos.index')->withVideo($videos);
        }
        return redirect()->route('auth.login');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('videos.new');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title'     => 'required|min:3',
            'video-url'     => 'required|min:3',
            'description'    => 'required|min:10',
            'category'   => 'required'
        ]);

        $haystacks = ['=', '/'];

        foreach ($haystacks as $haystack) {
            $video_url = substr(trim($request->input('video-url')), strrpos(trim($request->input('video-url')), $haystack, -1) + 1);

            if ($this->videoExist($video_url)) {
                $video = new Video;
                $video->video_title = trim($request->input('title'));
                $video->video_category = trim($request->input('category'));
                $video->video_url = $video_url;
                $video->video_description = trim($request->input('description'));
                $video->user_id = Auth::user()->id;

                $video->save();
            }
        }
        return redirect()->route('videos.index');
    }

    protected function videoExist($video_url)
    {
        $theURL = "http://www.youtube.com/oembed?url=http://www.youtube.com/watch?v=$video_url&format=json";
        $headers = get_headers($theURL);

        return (substr($headers[0], 9, 3) !== "404") ? true : false;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $video = Video::find($id);
        return view('videos.show')->withVideo($video);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $video_id = $request['video_id'];
        $video = Video::find($video_id);
        $this->validate($request, [
            'title'     => 'required|min:3',
            'video-url'     => 'required|min:3',
            'description'    => 'required|min:10',
            'category'   => 'required'
        ]);

        $haystacks = ['=', '/'];

        foreach ($haystacks as $haystack) {
            $video_url = substr(trim($request['video-url']), strrpos(trim($request['video-url']), $haystack, -1) + 1);

            if ($this->videoExist($video_url)) {
                $video->video_url = $video_url;
            }
        }

        $video->video_title = $request['title'];
        $video->video_category = $request['category'];
        $video->video_description = $request['description'];
        $video->save();

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $video_id = $request['video_id'];

        Video::destroy($video_id);

        return redirect()->route('videos.index');
    }
}
