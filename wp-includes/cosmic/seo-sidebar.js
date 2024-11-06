// Add to wp-includes/cosmic/js/cosmic-seo-sidebar.js

const { registerPlugin } = wp.plugins;
const { PluginSidebar } = wp.editPost;
const { TextControl, TextareaControl } = wp.components;
const { useSelect, useDispatch } = wp.data;
const { useEffect, useState } = wp.element;

const CosmicSEOSidebar = () => {
    const [seoTitle, setSeoTitle] = useState('');
    const [seoDescription, setSeoDescription] = useState('');
    const [seoKeywords, setSeoKeywords] = useState('');

    const postId = useSelect(select => select('core/editor').getCurrentPostId());
    
    // Load initial data
    useEffect(() => {
        wp.apiFetch({
            path: `/cosmic-seo/v1/post/${postId}`,
            method: 'GET',
        }).then(response => {
            setSeoTitle(response.seo_title || '');
            setSeoDescription(response.seo_description || '');
            setSeoKeywords(response.seo_keywords || '');
        });
    }, [postId]);

    // Save data when post is saved
    const { savePost } = useDispatch('core/editor');
    useEffect(() => {
        const unsubscribe = wp.data.subscribe(() => {
            const isSaving = wp.data.select('core/editor').isSavingPost();
            const isAutosaving = wp.data.select('core/editor').isAutosavingPost();
            
            if (isSaving && !isAutosaving) {
                wp.apiFetch({
                    path: `/cosmic-seo/v1/post/${postId}`,
                    method: 'POST',
                    data: {
                        seo_title: seoTitle,
                        seo_description: seoDescription,
                        seo_keywords: seoKeywords,
                        _wpnonce: cosmicSEO.nonce
                    }
                });
            }
        });

        return () => unsubscribe();
    }, [seoTitle, seoDescription, seoKeywords]);

    return (
        <PluginSidebar
            name="cosmic-seo-sidebar"
            title="SEO Settings"
            icon="admin-site"
        >
            <div className="cosmic-seo-sidebar-content">
                <TextControl
                    label="SEO Title"
                    value={seoTitle}
                    onChange={setSeoTitle}
                    help="Recommended length: 50-60 characters"
                />
                <TextareaControl
                    label="SEO Description"
                    value={seoDescription}
                    onChange={setSeoDescription}
                    help="Recommended length: 150-160 characters"
                />
                <TextControl
                    label="SEO Keywords"
                    value={seoKeywords}
                    onChange={setSeoKeywords}
                    help="Separate keywords with commas"
                />
            </div>
        </PluginSidebar>
    );
};

registerPlugin('cosmic-seo', {
    render: CosmicSEOSidebar,
    icon: 'admin-site'
});