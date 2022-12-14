jQuery( function( $ ) {
	$('.acf-input').on('click', '.crdi-create-image-btn', function() {
		const widthElm = $(this).parent().find('.crdi-input[name="width"]')
		const heightElm = $(this).parent().find('.crdi-input[name="height"]')

		const width = $(widthElm).val()
		const height = $(heightElm).val()

		$.ajax({
			url: ajaxUrl,
			type: 'post',
			dataType:'json',
			data: {
				action: 'create_and_register_dummy_image',
				width,
				height
			}
		})
		.done(function(response) {
			alert("画像の生成に成功しました。\n" + '生成された画像の名前: ' + response.image_name)
		})
		.fail(function(xhr) {
			alert('画像の生成に失敗しました。')
		})
	})

	// 画像登録する時に使えそう
	// 'input[name="acf[field_63997c480bfa0]"]'
} );