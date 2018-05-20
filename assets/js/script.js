// Extends the Groups Invite UI
( function( bp ) {

	if ( ! bp.Nouveau || ! bp.Nouveau.GroupInvites ) {
		return;
	}

	window.onbeforeunload = function() {
		if ( bp.Nouveau.GroupInvites.invites && bp.Nouveau.GroupInvites.invites.length ) {
			return false;
		}
	};

} )( window.bp || {} );
