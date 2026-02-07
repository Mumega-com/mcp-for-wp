<?php

use PHPUnit\Framework\TestCase;

final class SanitizationWrapperTagsTest extends TestCase {
	public function test_contains_wrapper_html_tags_detects_body_tag(): void {
		$subject = new Spai_Sanitization_Test_Double();
		$this->assertTrue( $subject->contains_wrapper_html_tags_public( '<body><div>ok</div>' ) );
	}

	public function test_contains_wrapper_html_tags_ignores_normal_markup(): void {
		$subject = new Spai_Sanitization_Test_Double();
		$this->assertFalse( $subject->contains_wrapper_html_tags_public( '<div><span>ok</span></div>' ) );
	}

	public function test_strip_wrapper_html_tags_removes_body_and_keeps_inner_html(): void {
		$subject = new Spai_Sanitization_Test_Double();
		$result  = $subject->strip_wrapper_html_tags_public( '<body class="x"><div>ok</div></body>' );

		$this->assertTrue( $result['changed'] );
		$this->assertSame( '<div>ok</div>', $result['content'] );
	}

	public function test_strip_wrapper_html_tags_is_idempotent_when_no_tags(): void {
		$subject = new Spai_Sanitization_Test_Double();
		$result  = $subject->strip_wrapper_html_tags_public( '<div>ok</div>' );

		$this->assertFalse( $result['changed'] );
		$this->assertSame( '<div>ok</div>', $result['content'] );
	}
}

final class Spai_Sanitization_Test_Double {
	use Spai_Sanitization;

	public function contains_wrapper_html_tags_public( $content ) {
		return $this->contains_wrapper_html_tags( $content );
	}

	public function strip_wrapper_html_tags_public( $content ) {
		return $this->strip_wrapper_html_tags( $content );
	}
}
