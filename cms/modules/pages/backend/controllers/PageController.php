<?php
/**
 * @link http://www.bigbrush-agency.com/
 * @copyright Copyright (c) 2015 Big Brush Agency ApS
 * @license http://www.bigbrush-agency.com/license/
 */

namespace cms\modules\pages\backend\controllers;

use Yii;
use yii\base\InvalidCallException;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\View;
use cms\modules\pages\models\Page;

/**
 * PageController
 */
class PageController extends Controller
{
    /**
     * Lists all available pages
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Page::find()->with(['category']),
        ]);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates and edits a page.
     *
     * @param int optional id of a page to edit. If id is not provided
     * a new page is created.
     * @return string
     */
    public function actionEdit($id = 0)
    {
        $model = new Page();
        $categories = [];
        foreach (Yii::$app->big->categoryManager->getCategories() as $category) {
            $categories[$category->id] = str_repeat('- ', $category->depth - 1) . $category->title;
        }
        if ($id) {
            $model = Page::find()->where(['id' => $id])->with(['author', 'editor'])->one();
        }
        $request = Yii::$app->getRequest();
        if ($model->load($request->post()) && $model->save()) {
            Yii::$app->getSession()->setFlash('success', Yii::t('cms', 'Page saved'));
            if (Yii::$app->toolbar->stayAfterSave()) {
                return $this->redirect(['edit', 'id' => $model->id]);
            } else {
                return $this->redirect(['index']);
            }
        }
        $templates = Yii::$app->big->templateManager->getDropDownList();
        return $this->render('edit', [
            'model' => $model,
            'templates' => $templates,
            'categories' => $categories,
        ]);
    }

    /**
     * Deletes a page after a form submission.
     *
     * @param int $id an id of a page to delete. Must match id in posted form.
     * @throws InvalidCallException if provided id does not match id in posted form.
     */
    public function actionDelete($id)
    {
        $pageId = Yii::$app->getRequest()->post('id');
        if ($pageId != $id) {
            throw new InvalidCallException("Invalid form submitted. Page with id: '$id' not deleted.");
        }
        $model = Page::findOne($id);
        if ($model) {
            if ($model->delete()) {
                Yii::$app->getSession()->setFlash('success', Yii::t('cms', 'Page deleted.'));
            } else {
                Yii::$app->getSession()->setFlash('info', Yii::t('cms', 'Page not deleted, please try again.'));
            }
        } else {
            Yii::$app->getSession()->setFlash('error', Yii::t('cms', 'Page with id "{id}" not found.', [
                'id' => $id
            ]));
        }
        return $this->redirect(['index']);
    }
}