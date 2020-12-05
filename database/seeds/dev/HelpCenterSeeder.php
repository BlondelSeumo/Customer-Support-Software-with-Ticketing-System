<?php

use App\Article;
use App\Category;
use Illuminate\Database\Seeder;

class HelpCenterSeeder extends Seeder
{
    private $categories = [
        'BeMusic - Music Streaming Engine',
        'BeDrive - File Sharing and Cloud Storage',
        'Mercury - Theme for MTDb (Movies and TV Database)',
        'Architect Lite - HTML Builder (Front-End Version)',
        'Picturish - Image hosting, editing and sharing',
        'Pixie - Image Editor',
        'Architect - HTML and Site Builder',
        'Origins - Video Games Portal',
        'MTDb - Ultimate Movie&TV Database'
    ];

    private $children = ['Web Design', 'App Development', 'Graphic Design', 'Photography', 'Motion Graphics', 'Team Speaking Tricks', 'Dreamweaver', 'Customization', 'Tips Tricks'];

    private $articles = [
        ['How to install WordPress?', 'Creating your first database / mysql', 'Installation theme via FTP', 'Installation of the necessary add-ons', 'Import demo content like our demo'],
        ['How to change color schemes?', 'Using Google web fonts for all sections', 'Introduction theme setups', 'Menu color and text change in one panel', 'Using child theme for custom styles', 'Changing footer and copyright colors'],
        ['3rd party recommended plugins', 'Adding custom social share buttons', 'Adding newsletter plugin', 'Removing preloader effect'],
        ['How to use this documentation?', 'How to find topics?', 'What is included and why?', 'Basic knowledge requirements.', 'Getting Started & What is next.'],
        ['Installation & Activation', 'Premium Members Features', 'API Usage & Guide lines', 'Getting Started & What is next.', 'Installation & Activation', 'Premium Members Features', 'API Usage & Guide lines', 'Getting Started & What is next.'],
        ['Wix Overview', 'Getting Started With Wix', 'Signing Up With Wix', 'Examples of Sites Built With Wix', 'Accessing Wix Site Templates', 'Selecting a Template: Viewing and Editing a Wix Template', 'Creating a Wix Site From a Blank Template', 'Selecting a Template: Searching for a Template'],
        ['Templates for Musicians', 'Templates for Professionals', 'Templates for Food Industry Professionals', 'The Wix Editor', 'Hiring a Designer to Create Your Site', 'Rebuilding Your Site', 'Purchasing and Exporting a Wix Template'],
        ['About the Header', 'About the Footer', 'Site Width', 'Save, Preview and Publish Your Site', 'Editing Your Site After Saving or Publishing', 'Icons in the Editor'],
        ['Getting Started with the Wix Editor', 'Using the Editor Top Bar', 'Editor Tools', 'The Right-Click Menu', 'Adding Elements to Your Site', 'Arranging the Content on Your Sites Pages'],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach($this->categories as $category) {
            $category = Category::firstOrCreate(['name' => $category]);

            $childrenIds = array_map(function($name) use($category) {
                return Category::firstOrCreate(['name' => $name], ['name' => $name, 'parent_id' => $category->id])->id;
            }, $this->children);

            foreach($childrenIds as $k => $id) {
                $articleIds = array_map(function($articleName) {
                    return Article::firstOrCreate(['title' => $articleName])->id;
                }, $this->articles[$k]);

                Category::find($id)->articles()->sync($articleIds);
                $category->articles()->sync($articleIds);
            }
        }
    }
}
